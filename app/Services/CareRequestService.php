<?php

namespace App\Services;

use App\Exceptions\CareRequest\CareRequestNotFoundException;
use App\Exceptions\CareRequest\InvalidCareRequestStateException;
use App\Exceptions\CareRequest\DuplicateBookingException;
use App\Models\CareRequest;
use App\Models\CareType;
use App\Models\NurseRequestCache;
use App\Helpers\BiddingWindow;
use App\Models\RequestBid;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CareRequestService
{
    protected ProviderMatchingService $matchingService;

    public function __construct(ProviderMatchingService $matchingService)
    {
        $this->matchingService = $matchingService;
    }

    /**
     * List care requests for a user (paginated).
     */
    public function listForUser(int $userId)
    {
        return CareRequest::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    /**
     * Create a care request and run nurse matching.
     *
     * 
     */
    public function createAndMatch(array $data, int $userId): array
    {
        // 1. Create the care request
        $careRequest = $this->createRequestRecord($data, $userId);

        // 2. Find eligible nurses iteratively
        return $this->runMatching($careRequest);
    }

    /**
     * Run matching for an existing care request.
     */
    public function runMatching(CareRequest $careRequest): array
    {
        $matchResult = $this->matchingService->runIterativeMatching($careRequest);
        $eligibleNurses = $matchResult['nurses'];
        $matchedLevel = $matchResult['level'];

        // 3. Handle case where no nurses are found
        if ($eligibleNurses->isEmpty()) {
            $careRequest->update([
                'status' => CareRequest::STATUS_FAILED_NO_NURSES,
                'matching_attempt_level' => $matchedLevel,
            ]);

            throw new InvalidCareRequestStateException(
                'No nurses available for the requested time and location. Please try adjusting your schedule or location.',
                422
            );
        }

        // 4. Calculate bidding window expiration
        $expiresAt = BiddingWindow::calculateExpiry($careRequest);

        // 5. Save matching records to cache for notifications
        $this->createRequestCacheEntries($careRequest, $eligibleNurses, $matchedLevel, $expiresAt);

        // 6. Update request status to MATCHING
        $careRequest->update([
            'status' => CareRequest::STATUS_MATCHING,
            'bidding_ends_at' => $expiresAt,
            'matching_attempt_level' => $matchedLevel,
        ]);

        return [
            'care_request' => $careRequest,
            'final_nurses_count' => $eligibleNurses->count(),
            'matching_level' => $matchedLevel,
        ];
    }

    /**
     * Update a care request based on its status.
     */
    public function updateRequest(int $requestId, array $data, int $userId): array
    {
        $careRequest = CareRequest::where('id', $requestId)->where('user_id', $userId)->first();

        if (!$careRequest) {
            throw new CareRequestNotFoundException('Care request not found.', 404);
        }

        // If status is MATCHING, only allow notes and secondary_phone
        if ($careRequest->status === CareRequest::STATUS_MATCHING) {
            $allowedEdits = array_intersect_key($data, array_flip(['notes', 'secondary_phone']));

            // Check if user is trying to edit restricted fields
            $restrictedFields = array_diff_key($data, array_flip(['notes', 'secondary_phone']));
            if (!empty($restrictedFields)) {
                throw new InvalidCareRequestStateException('Only notes and secondary phone can be updated while nurses are matching.', 422);
            }

            if (!empty($allowedEdits)) {
                $careRequest->update($allowedEdits);
            }

            return [
                'care_request' => $careRequest,
                'message' => 'Request notes/phone updated successfully.',
                'matching_restarted' => false
            ];
        }

        // If status is PENDING or FAILED_NO_NURSES, allow full edit and re-run matching
        if (
            in_array($careRequest->status, [
                CareRequest::STATUS_FAILED_NO_NURSES,
                CareRequest::STATUS_PENDING
            ])
        ) {
            $careRequest->update($data);

            // Cleanup old failed attempts to start fresh
            NurseRequestCache::where('care_request_id', $careRequest->id)->delete();

            // Re-run matching
            $result = $this->runMatching($careRequest);
            $result['message'] = 'Request updated and matching restarted successfully.';
            $result['matching_restarted'] = true;
            return $result;
        }

        throw new InvalidCareRequestStateException('This request cannot be edited in its current state.', 422);
    }

    /**
     * Cancel an active care request.
     */
    public function cancelRequest(int $requestId, int $userId, ?string $reason = null): CareRequest
    {
        $careRequest = CareRequest::where('id', $requestId)->where('user_id', $userId)->first();

        if (!$careRequest) {
            throw new CareRequestNotFoundException('Care request not found.', 404);
        }

        if (in_array($careRequest->status, [CareRequest::STATUS_CANCELLED, CareRequest::STATUS_COMPLETED])) {
            throw new InvalidCareRequestStateException('This request cannot be cancelled.', 422);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($careRequest, $reason) {
            $careRequest->update([
                'status' => CareRequest::STATUS_CANCELLED,
                'cancelled_by' => CareRequest::CANCELLED_BY_USER,
                'cancel_reason' => $reason
            ]);

            // Expire cache entries so nurses no longer see it
            NurseRequestCache::where('care_request_id', $careRequest->id)
                ->whereIn('status', [NurseRequestCache::STATUS_NOTIFIED, NurseRequestCache::STATUS_VIEWED])
                ->update(['status' => NurseRequestCache::STATUS_EXPIRED]);

            // Cancel any pending bids
            RequestBid::where('care_request_id', $careRequest->id)
                ->whereIn('status', [RequestBid::STATUS_PENDING])
                ->update(['status' => RequestBid::STATUS_CANCELLED]);
        });

        return $careRequest;
    }

    /**
     * Create the CareRequest record with validated data.
     */
    private function createRequestRecord(array $data, int $userId): CareRequest
    {
        $data['user_id'] = $userId;
        $data['reference_id'] = 'REQ-' . now()->format('ymd') . '-' . strtoupper(Str::random(4));
        $data['status'] = CareRequest::STATUS_PENDING;

        // Snapshot commission settings from the CareType
        $careType = CareType::findOrFail($data['care_type_id']);
        $data['commission_type'] = $careType->commision_type;
        $data['commission_value'] = $careType->commision_value;

        return CareRequest::create($data);
    }

    /**
     * Generate cache entries for matched nurses to receive notifications/bids.
     */
    private function createRequestCacheEntries(CareRequest $careRequest, $nurses, int $matchedLevel, Carbon $expiresAt): void
    {
        $now = now();
        $baseSnapshot = [
            'care_request_ref' => $careRequest->reference_id,
            'start_date' => $careRequest->start_date,
            'end_date' => $careRequest->end_date,
            'start_time' => $careRequest->start_time,
            'end_time' => $careRequest->end_time,
            'city' => $careRequest->city,
            'pincode' => $careRequest->pincode,
            'care_type' => $careRequest->careType->name ?? 'Unknown',
        ];

        $records = [];
        foreach ($nurses as $nurse) {
            $records[] = [
                'nurse_id' => $nurse->id,
                'care_request_id' => $careRequest->id,
                'request_snapshot' => json_encode(array_merge($baseSnapshot, [
                    'distance_to_patient' => isset($nurse->distance) ? round($nurse->distance, 2) : null,
                    'matched_level' => $matchedLevel,
                ])),
                'status' => NurseRequestCache::STATUS_NOTIFIED,
                'notified_at' => $now,
                'expires_at' => $expiresAt,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (isset($nurse->user)) {
                $nurse->user->notify(new \App\Notifications\NewCareRequestNotification($careRequest));
            }
        }

        if (!empty($records)) {
            NurseRequestCache::insert($records);
        }
    }
}
