<?php

namespace App\Services;

use App\Exceptions\CareRequest\CareRequestNotFoundException;
use App\Exceptions\CareRequest\InvalidCareRequestStateException;
use App\Exceptions\Bidding\BidNotFoundException;
use App\Models\CareRequest;
use App\Models\CareType;
use App\Models\NurseRequestCache;
use App\Models\RequestBid;
use App\Helpers\CommissionCalculator;
use Illuminate\Support\Facades\DB;

class BiddingService
{
    /**
     * Place a bid on a care request.
     *
     * @throws \\Exception
     */
    public function placeBid(int $nurseProfileId, array $data): RequestBid
    {
        $careRequestId = $data['care_request_id'];
        $nurseAmount = (float) $data['nurse_amount'];

        // 1. Verify care request exists and is in MATCHING status
        $careRequest = CareRequest::find($careRequestId);

        if (!$careRequest) {
            throw new CareRequestNotFoundException('Care request not found.', 404);
        }

        if ($careRequest->status !== CareRequest::STATUS_MATCHING) {
            throw new InvalidCareRequestStateException('This care request is no longer accepting bids.', 409);
        }

        // 2. Verify nurse was notified for this request (exists in cache)
        $cacheEntry = NurseRequestCache::where('nurse_id', $nurseProfileId)
            ->where('care_request_id', $careRequestId)
            ->whereIn('status', [
                NurseRequestCache::STATUS_NOTIFIED,
                NurseRequestCache::STATUS_VIEWED,
            ])
            ->first();

        if (!$cacheEntry) {
            throw new InvalidCareRequestStateException('You are not eligible to bid on this request.', 403);
        }

        // 3. Check if bidding window has expired
        if ($cacheEntry->expires_at && $cacheEntry->expires_at->isPast()) {
            throw new InvalidCareRequestStateException('Bidding window has expired for this request.', 410);
        }

        // 4. Check duplicate bid
        $existingBid = RequestBid::where('care_request_id', $careRequestId)
            ->where('nurse_id', $nurseProfileId)
            ->exists();

        if ($existingBid) {
            throw new InvalidCareRequestStateException('You have already placed a bid on this request.', 409);
        }

        // 5. Calculate commission using single source of truth helper
        $commissionType = (int) ($careRequest->commission_type ?? 1);
        $commissionValue = (float) ($careRequest->commission_value ?? 0);

        // Calculate days for per-day commission calculations
        $days = 1;
        if ($commissionType === CareType::COMMISION_TYPE_FIXED_PER_DAY) {
            $startDate = \Carbon\Carbon::parse($careRequest->start_date)->startOfDay();
            $endDate = \Carbon\Carbon::parse($careRequest->end_date ?? $careRequest->start_date)->startOfDay();
            $days = $startDate->diffInDays($endDate) + 1;
        }

        $pricing = CommissionCalculator::calculateWithTotal($nurseAmount, $commissionType, $commissionValue, $days);
        $commissionAmount = $pricing['commission_amount'];
        $totalAmount = $pricing['total_amount'];

        // 6. Get distance from cache snapshot
        $snapshot = is_string($cacheEntry->request_snapshot)
            ? json_decode($cacheEntry->request_snapshot, true)
            : ($cacheEntry->request_snapshot ?? []);
        $distanceKm = $snapshot['distance_to_patient'] ?? null;

        // 7. Save bid + update cache in a transaction
        return DB::transaction(function () use ($careRequestId, $nurseProfileId, $nurseAmount, $commissionType, $commissionValue, $commissionAmount, $totalAmount, $distanceKm, $cacheEntry, $data) {
            $bid = RequestBid::create([
                'care_request_id' => $careRequestId,
                'nurse_id' => $nurseProfileId,
                'nurse_amount' => $nurseAmount,
                'commission_type' => $commissionType,
                'commission_value' => $commissionValue,
                'commission_amount' => $commissionAmount,
                'total_amount' => $totalAmount,
                'notes' => $data['notes'] ?? null,
                'distance_km' => $distanceKm,
                'expires_at' => $cacheEntry->expires_at,
                'status' => RequestBid::STATUS_PENDING,
            ]);

            // Mark cache entry as bid placed
            $cacheEntry->update(['status' => NurseRequestCache::STATUS_BID_PLACED]);

            // Increment bid counter on care request
            $careRequest = CareRequest::find($careRequestId);
            $careRequest->increment('total_bids_received');
            
            // Notify patient
            $careRequest->user->notify(new \App\Notifications\NewBidPlacedNotification($bid));

            return $bid;
        });
    }

    /**
     * Get bids for a care request (user-facing — limited nurse info).
     *
     * @throws \\Exception
     */
    public function getBidsForUser(int $careRequestId, int $userId): array
    {
        $careRequest = CareRequest::where('id', $careRequestId)
            ->where('user_id', $userId)
            ->first();

        if (!$careRequest) {
            throw new CareRequestNotFoundException('Care request not found.', 404);
        }

        $bids = RequestBid::where('care_request_id', $careRequestId)
            ->where('status', RequestBid::STATUS_PENDING)
            ->with(['nurse:id,user_id,bio,avg_rating,total_reviews', 'nurse.user:id,name,profile_photo'])
            ->get();

        // Sort by highest rating first
        $bids = $bids->sortByDesc(fn($bid) => $bid->nurse?->avg_rating ?? 0)->values();

        $formatted = $bids->map(function (RequestBid $bid) {
            $nurse = $bid->nurse;
            $user = $nurse?->user;

            return [
                'bid_id' => $bid->id,
                'nurse_name' => $user?->name ?? 'Unknown',
                'nurse_photo' => $user?->profile_photo ? asset('storage/' . $user->profile_photo) : null,
                'nurse_bio' => $nurse?->bio,
                'avg_rating' => $nurse?->avg_rating ?? 0,
                'total_reviews' => $nurse?->total_reviews ?? 0,
                'total_amount' => $bid->total_amount,
                'notes' => $bid->notes,
                'created_at' => $bid->created_at->toDateTimeString(),
            ];
        });

        return [
            'care_request_id' => $careRequest->id,
            'reference_id' => $careRequest->reference_id,
            'status' => $careRequest->status,
            'status_text' => $careRequest->status_text,
            'total_bids' => $formatted->count(),
            'bids' => $formatted->toArray(),
        ];
    }

    /**
     * Get a single bid for a care request (user-facing — limited nurse info + experience).
     *
     * @throws \\Exception
     */
    public function getBidForUser(int $careRequestId, int $bidId, int $userId): array
    {
        $careRequest = CareRequest::where('id', $careRequestId)
            ->where('user_id', $userId)
            ->first();

        if (!$careRequest) {
            throw new CareRequestNotFoundException('Care request not found.', 404);
        }

        $bid = RequestBid::where('id', $bidId)
            ->where('care_request_id', $careRequestId)
            ->with(['nurse:id,user_id,bio,years_of_experience,avg_rating,total_reviews', 'nurse.user:id,name,profile_photo'])
            ->first();

        if (!$bid) {
            throw new BidNotFoundException('Bid not found.', 404);
        }

        $nurse = $bid->nurse;
        $user = $nurse?->user;

        return [
            'bid_id' => $bid->id,
            'care_request_id' => $bid->care_request_id,
            'nurse_name' => $user?->name ?? 'Unknown',
            'nurse_photo' => $user?->profile_photo ? asset('storage/' . $user->profile_photo) : null,
            'years_of_experience' => $nurse?->years_of_experience ?? 0,
            'nurse_bio' => $nurse?->bio,
            'avg_rating' => $nurse?->avg_rating ?? 0,
            'total_reviews' => $nurse?->total_reviews ?? 0,
            'total_amount' => $bid->total_amount,
            'notes' => $bid->notes,
            'status' => $bid->status,
            'created_at' => $bid->created_at->toDateTimeString(),
        ];
    }

    /**
     * Get all bids placed by a nurse.
     */
    public function getNurseBids(int $nurseProfileId, $perPage = 10)
    {
        return RequestBid::where('nurse_id', $nurseProfileId)
            ->with(['careRequest'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(function ($bid) {
                return [
                    'bid_id' => $bid->id,
                    'care_request_id' => $bid->care_request_id,
                    'total_amount' => $bid->total_amount,
                    'notes' => $bid->notes,
                    'status' => $bid->status,
                    'status_text' => $bid->status_text,
                    'status_color' => $bid->status_color,
                    'created_at' => $bid->created_at->toDateTimeString(),
                    'care_request_snapshot' => $bid->request_snapshot,
                    'care_request_current_status' => $bid->careRequest?->status_text ?? 'Unknown',
                ];
            });
    }
}
