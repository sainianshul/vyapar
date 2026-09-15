<?php

namespace App\Services;

use App\Exceptions\Booking\BookingNotFoundException;
use App\Exceptions\Booking\InvalidBookingStateException;
use App\Exceptions\Booking\SessionNotFoundException;
use App\Exceptions\Booking\InvalidSessionStateException;
use App\Exceptions\Booking\InvalidSessionOtpException;
use App\Exceptions\CareRequest\CareRequestNotFoundException;
use App\Exceptions\CareRequest\InvalidCareRequestStateException;
use App\Exceptions\CareRequest\DuplicateBookingException;
use App\Exceptions\Bidding\BidNotFoundException;
use App\Models\Booking;
use App\Models\BookingSession;
use App\Models\CareRequest;
use App\Models\RequestBid;
use App\Models\WalletTransaction;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Handles booking lifecycle: creation, session generation, session start/end, completion.
 *
 * Payment is handled by PaymentService — this service DOES NOT touch money directly
 * except for nurse payout on booking completion (via WalletService).
 *
 * Flow:
 *   1. User selects bid       → createFromBid()     → Booking(pending_payment)
 *   2. PaymentService handles payment confirmation
 *   3. After payment          → generateSessions()   → BookingSessions created
 *   4. Daily OTP cycle        → startSession() / endSession()
 *   5. All sessions done      → completeBooking()    → Nurse gets paid
 */
class BookingService
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Create a booking from a selected bid.
     *
     * @throws \Exception
     */
    public function createFromBid(int $careRequestId, int $bidId, int $userId): Booking
    {
        $careRequest = CareRequest::where('id', $careRequestId)
            ->where('user_id', $userId)
            ->first();

        if (!$careRequest) {
            throw new CareRequestNotFoundException('Care request not found.', 404);
        }

        if (!in_array($careRequest->status, [CareRequest::STATUS_MATCHING, CareRequest::STATUS_ACCEPTED])) {
            throw new InvalidCareRequestStateException('This care request is not in a valid state for booking.', 409);
        }

        // Prevent duplicate bookings for same care request
        $existingBooking = Booking::where('care_request_id', $careRequestId)
            ->whereNotIn('status', [Booking::STATUS_CANCELLED])
            ->exists();

        if ($existingBooking) {
            throw new DuplicateBookingException('A booking already exists for this care request.', 409);
        }

        $bid = RequestBid::where('id', $bidId)
            ->where('care_request_id', $careRequestId)
            ->where('status', RequestBid::STATUS_PENDING)
            ->first();

        if (!$bid) {
            throw new BidNotFoundException('Bid not found or already processed.', 404);
        }

        // Calculate total sessions (days)
        $startDate = $careRequest->start_date;
        $endDate = $careRequest->end_date ?? $careRequest->start_date;
        $totalSessions = $startDate->diffInDays($endDate) + 1;

        return DB::transaction(function () use ($careRequest, $bid, $userId, $totalSessions) {
            // 1. Create booking
            $booking = Booking::create([
                'reference_id' => 'BKG-' . now()->format('ymd') . '-' . strtoupper(Str::random(4)),
                'care_request_id' => $careRequest->id,
                'bid_id' => $bid->id,
                'user_id' => $userId,
                'nurse_id' => $bid->nurse_id,
                'nurse_amount' => $bid->nurse_amount,
                'commission_amount' => $bid->commission_amount,
                'total_amount' => $bid->total_amount,
                'start_date' => $careRequest->start_date,
                'end_date' => $careRequest->end_date ?? $careRequest->start_date,
                'start_time' => $careRequest->start_time,
                'end_time' => $careRequest->end_time,
                'total_sessions' => $totalSessions,
                'completed_sessions' => 0,
                'status' => Booking::STATUS_PENDING_PAYMENT,
                'payment_status' => Booking::PAYMENT_UNPAID,
                'patient_name' => $careRequest->patient_name,
                'patient_age' => $careRequest->patient_age,
                'contact_phone' => $careRequest->contact_phone,
                'secondary_phone' => $careRequest->secondary_phone,
                'care_type_name' => $careRequest->careType ? $careRequest->careType->name : null,
                'address' => $careRequest->address,
                'city' => $careRequest->city,
                'state' => $careRequest->state,
                'country' => $careRequest->country,
                'pincode' => $careRequest->pincode,
                'latitude' => $careRequest->latitude,
                'longitude' => $careRequest->longitude,
            ]);

            // 2. Mark bid as selected, reject all others
            $bid->update(['status' => RequestBid::STATUS_SELECTED]);

            RequestBid::where('care_request_id', $careRequest->id)
                ->where('id', '!=', $bid->id)
                ->where('status', RequestBid::STATUS_PENDING)
                ->update(['status' => RequestBid::STATUS_REJECTED]);

            // 3. Update care request status
            $careRequest->update(['status' => CareRequest::STATUS_ACCEPTED]);
            
            // Notify winning nurse
            // Notification removed as per request

            return $booking;
        });
    }

    /**
     * Generate daily sessions for a confirmed booking.
     * Called by PaymentService after payment is confirmed.
     */
    public function generateSessions(Booking $booking): void
    {
        // Safety: don't generate sessions twice
        if (BookingSession::where('booking_id', $booking->id)->exists()) {
            return;
        }

        $period = CarbonPeriod::create($booking->start_date, $booking->end_date);
        $sessionNumber = 1;
        $records = [];
        $now = now();

        foreach ($period as $date) {
            $startOtp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $endOtp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $records[] = [
                'booking_id' => $booking->id,
                'session_date' => $date->format('Y-m-d'),
                'session_number' => $sessionNumber++,
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'start_otp' => $startOtp,
                'end_otp' => $endOtp,
                'status' => BookingSession::STATUS_UPCOMING,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($records)) {
            BookingSession::insert($records);
        }
    }

    /**
     * Get booking details for a user.
     *
     * @throws \Exception
     */
    public function getBookingForUser(int $bookingId, int $userId): Booking
    {
        $booking = Booking::where('id', $bookingId)
            ->where('user_id', $userId)
            ->with(['sessions', 'nurse.user:id,name,profile_photo', 'careRequest.careType:id,name'])
            ->first();

        if (!$booking) {
            throw new BookingNotFoundException('Booking not found.', 404);
        }

        return $booking;
    }

    /**
     * List bookings for a user (paginated).
     */
    public function listForUser(int $userId)
    {
        return Booking::where('user_id', $userId)
            ->with(['nurse.user:id,name,profile_photo', 'careRequest.careType:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    /**
     * Get nurse's schedule for a specific date.
     */
    public function getNurseSchedule(int $nurseId, string $date)
    {
        return BookingSession::whereHas('booking', function ($query) use ($nurseId) {
            $query->where('nurse_id', $nurseId)
                ->whereIn('status', [
                    Booking::STATUS_CONFIRMED,
                    Booking::STATUS_ACTIVE,
                ]);
        })
            ->where('session_date', $date)
            ->whereIn('status', [
                BookingSession::STATUS_UPCOMING,
                BookingSession::STATUS_STARTED,
            ])
            ->with(['booking.user:id,name,phone', 'booking.careRequest.careType:id,name'])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * List bookings for a nurse (paginated).
     */
    public function listForNurse(int $nurseId)
    {
        return Booking::where('nurse_id', $nurseId)
            ->whereIn('status', [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_ACTIVE,
                Booking::STATUS_COMPLETED,
            ])
            ->with(['user:id,name,phone', 'careRequest.careType:id,name'])
            ->orderBy('start_date', 'desc')
            ->paginate(10);
    }

    /**
     * Start a session — nurse verifies start OTP from patient.
     *
     * @throws \Exception
     */
    public function startSession(int $sessionId, string $otp, int $nurseId): BookingSession
    {
        $session = BookingSession::whereHas('booking', function ($query) use ($nurseId) {
                $query->where('nurse_id', $nurseId);
            })
            ->where('id', $sessionId)
            ->first();

        if (!$session) {
            throw new SessionNotFoundException('Session not found.', 404);
        }

        if ($session->status !== BookingSession::STATUS_UPCOMING) {
            throw new InvalidSessionStateException('This session cannot be started.', 409);
        }

        if (!$session->verifyStartOtp($otp)) {
            throw new InvalidSessionOtpException('Invalid Start OTP.', 422);
        }

        $session->update([
            'status' => BookingSession::STATUS_STARTED,
            'otp_verified_at' => now(),
            'started_at' => now(),
        ]);

        // Mark booking as active if it's still confirmed
        $booking = $session->booking;
        if ($booking->status === Booking::STATUS_CONFIRMED) {
            $booking->update(['status' => Booking::STATUS_ACTIVE]);
        }
        
        if (isset($booking->user)) {
            $booking->user->notify(new \App\Notifications\SessionStartedNotification($session));
        }

        return $session->fresh();
    }

    /**
     * End a session — nurse marks it complete.
     * If all sessions done → booking completes and nurse gets paid.
     *
     * @throws \Exception
     */
    public function endSession(int $sessionId, int $nurseId, string $otp, ?string $notes = null): BookingSession
    {
        $session = BookingSession::whereHas('booking', function ($query) use ($nurseId) {
                $query->where('nurse_id', $nurseId);
            })
            ->where('id', $sessionId)
            ->first();

        if (!$session) {
            throw new SessionNotFoundException('Session not found.', 404);
        }

        if ($session->status !== BookingSession::STATUS_STARTED) {
            throw new InvalidSessionStateException('This session has not been started yet.', 409);
        }

        if (!$session->verifyEndOtp($otp)) {
            throw new InvalidSessionOtpException('Invalid End OTP.', 422);
        }

        return DB::transaction(function () use ($session, $notes) {
            $session->update([
                'status' => BookingSession::STATUS_COMPLETED,
                'ended_at' => now(),
                'nurse_notes' => $notes,
            ]);

            // Increment completed sessions on booking
            $booking = $session->booking;
            $booking->increment('completed_sessions');
            $booking->refresh();

            // If all sessions completed → complete booking and payout nurse
            if ($booking->completed_sessions >= $booking->total_sessions) {
                $this->completeBooking($booking);
            }
            
            if (isset($booking->user)) {
                $booking->user->notify(new \App\Notifications\SessionEndedNotification($session));
            }

            return $session->fresh();
        });
    }

    /**
     * Force end a session without OTP.
     * Nurse must be within the configured distance of the patient's location.
     *
     * @throws \Exception
     */
    public function forceEndSession(int $sessionId, int $nurseId, string $reason, float $lat, float $lng): BookingSession
    {
        $session = BookingSession::whereHas('booking', function ($query) use ($nurseId) {
                $query->where('nurse_id', $nurseId);
            })
            ->with('booking')
            ->where('id', $sessionId)
            ->first();

        if (!$session) {
            throw new SessionNotFoundException('Session not found.', 404);
        }

        if ($session->status !== BookingSession::STATUS_STARTED) {
            throw new InvalidSessionStateException('This session has not been started yet.', 409);
        }

        $booking = $session->booking;

        // Calculate distance
        $bookingLat = (float) $booking->latitude;
        $bookingLng = (float) $booking->longitude;

        if ($bookingLat && $bookingLng) {
            $distance = $this->calculateDistance($lat, $lng, $bookingLat, $bookingLng);
            $maxDistance = (float) config('care.maximum_diameter_for_force_end', 100);

            if ($distance > $maxDistance) {
                throw new InvalidSessionStateException('You are too far from the patient location to force end this session. Max allowed: ' . $maxDistance . 'm. Your distance: ' . round($distance) . 'm.', 409);
            }
        }

        return DB::transaction(function () use ($session, $reason, $booking) {
            $session->update([
                'status' => BookingSession::STATUS_COMPLETED,
                'ended_at' => now(),
                'is_forced_end' => true,
                'force_end_reason' => $reason,
            ]);

            // Increment completed sessions on booking
            $booking->increment('completed_sessions');
            $booking->refresh();

            // If all sessions completed → complete booking and payout nurse
            if ($booking->completed_sessions >= $booking->total_sessions) {
                $this->completeBooking($booking);
            }
            
            if (isset($booking->user)) {
                $booking->user->notify(new \App\Notifications\SessionEndedNotification($session));
            }

            return $session->fresh();
        });
    }

    /**
     * Calculate distance between two points in meters using Haversine formula.
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // in meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    /**
     * Complete a booking — nurse gets full nurse_amount.
     */
    private function completeBooking(Booking $booking): void
    {
        $nursePayoutAmount = (float) $booking->nurse_amount;

        $booking->update([
            'status' => Booking::STATUS_COMPLETED,
            'nurse_payout_amount' => $nursePayoutAmount,
        ]);

        // Credit nurse wallet
        $this->walletService->credit(
            $booking->nurse->user_id,
            $nursePayoutAmount,
            WalletTransaction::REASON_NURSE_PAYOUT,
            "Payout for completed booking {$booking->reference_id}",
            $booking->id
        );
    }

}
