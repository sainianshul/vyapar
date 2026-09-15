<?php

namespace App\Services;

use App\Exceptions\Booking\BookingNotFoundException;
use App\Exceptions\Booking\InvalidBookingStateException;
use App\Exceptions\CareRequest\CareRequestNotFoundException;
use App\Exceptions\CareRequest\InvalidCareRequestStateException;
use App\Models\Booking;
use App\Models\BookingSession;
use App\Models\CareRequest;
use App\Models\NurseProfile;
use App\Contracts\PaymentGatewayInterface;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Handles all cancellation logic for bookings and care requests.
 *
 * Money Rules:
 *   - User cancels before payment     → Free cancel, no money movement
 *   - User cancels after payment      → Slab-based refund on remaining sessions
 *   - Nurse cancels after payment     → Full refund on remaining, nurse paid for completed only
 *   - Nurse payout uses nurse_per_session_rate (NOT total per_session_rate)
 *   - User refund uses total per_session_rate (what user actually paid)
 *   - Every refund/payout is tracked in wallet_transactions with booking_id
 */
class CancellationService
{
    protected WalletService $walletService;
    protected PaymentGatewayInterface $gateway;

    public function __construct(WalletService $walletService, PaymentGatewayInterface $gateway)
    {
        $this->walletService = $walletService;
        $this->gateway = $gateway;
    }

    /**
     * User cancels a booking.
     *
     * @throws \\Exception
     */
    public function cancelByUser(int $bookingId, int $userId, ?string $reason = null): array
    {
        $booking = Booking::where('id', $bookingId)
            ->where('user_id', $userId)
            ->first();

        if (!$booking) {
            throw new BookingNotFoundException('Booking not found.', 404);
        }

        if (!$booking->isCancellable()) {
            throw new InvalidBookingStateException('This booking cannot be cancelled.', 409);
        }

        return DB::transaction(function () use ($booking, $reason) {
            $refundAmount = 0.0;
            $nursePayoutAmount = 0.0;

            // Case 1: Not yet paid — free cancel
            if ($booking->status === Booking::STATUS_PENDING_PAYMENT) {
                return $this->performCancel($booking, Booking::CANCELLED_BY_USER, $reason, $refundAmount, $nursePayoutAmount);
            }

            // Case 2: Paid — calculate slab-based refund on remaining
            $remainingSessions = $booking->total_sessions - $booking->completed_sessions;

            if ($remainingSessions > 0) {
                $remainingUserAmount = round((float) $booking->per_session_rate * $remainingSessions, 2);
                $refundPercent = $this->getRefundPercent($booking);
                $refundAmount = round($remainingUserAmount * ($refundPercent / 100), 2);
            }

            // Nurse gets paid for completed sessions (using nurse rate, NOT total rate)
            if ($booking->completed_sessions > 0) {
                $nursePayoutAmount = round((float) $booking->nurse_per_session_rate * $booking->completed_sessions, 2);
            }

            // Process gateway refund for patient
            if ($refundAmount > 0) {
                if ($booking->gateway_payment_id) {
                    $amountInPaise = (int) round($refundAmount * 100);
                    try {
                        $this->gateway->createRefund($booking->gateway_payment_id, $amountInPaise, "User cancelled booking " . $booking->reference_id);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error("Gateway refund failed during user cancellation", [
                            "booking_id" => $booking->id,
                            "error" => $e->getMessage()
                        ]);
                        // Proceeding with cancellation even if refund fails (can be handled manually or retried)
                    }
                }
            }

            // Process nurse payout for completed sessions
            if ($nursePayoutAmount > 0) {
                $nurse = $booking->nurse;
                if ($nurse) {
                    $this->walletService->credit(
                        $nurse->user_id,
                        $nursePayoutAmount,
                        WalletTransaction::REASON_NURSE_PAYOUT,
                        "Payout for {$booking->completed_sessions} completed sessions — booking {$booking->reference_id}",
                        $booking->id
                    );
                }
            }

            return $this->performCancel($booking, Booking::CANCELLED_BY_USER, $reason, $refundAmount, $nursePayoutAmount);
        });
    }

    /**
     * Nurse cancels a booking.
     *
     * @throws \\Exception
     */
    public function cancelByNurse(int $bookingId, int $nurseId, ?string $reason = null): array
    {
        $booking = Booking::where('id', $bookingId)
            ->where('nurse_id', $nurseId)
            ->first();

        if (!$booking) {
            throw new BookingNotFoundException('Booking not found.', 404);
        }

        if (!$booking->isCancellable()) {
            throw new InvalidBookingStateException('This booking cannot be cancelled.', 409);
        }

        return DB::transaction(function () use ($booking, $reason, $nurseId) {
            $refundAmount = 0.0;
            $nursePayoutAmount = 0.0;

            // If user has paid, full refund for remaining sessions (100% — nurse's fault)
            if ($booking->isPaid()) {
                $remainingSessions = $booking->total_sessions - $booking->completed_sessions;
                $refundAmount = round((float) $booking->per_session_rate * $remainingSessions, 2);

                if ($refundAmount > 0) {
                    if ($booking->gateway_payment_id) {
                        $amountInPaise = (int) round($refundAmount * 100);
                        try {
                            $this->gateway->createRefund($booking->gateway_payment_id, $amountInPaise, "Nurse cancelled booking " . $booking->reference_id);
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::error("Gateway refund failed during nurse cancellation", [
                                "booking_id" => $booking->id,
                                "error" => $e->getMessage()
                            ]);
                        }
                    }
                }

                // Pay nurse for completed sessions only (nurse rate, not total)
                if ($booking->completed_sessions > 0) {
                    $nursePayoutAmount = round((float) $booking->nurse_per_session_rate * $booking->completed_sessions, 2);
                    $nurse = NurseProfile::find($nurseId);

                    if ($nurse) {
                        $this->walletService->credit(
                            $nurse->user_id,
                            $nursePayoutAmount,
                            WalletTransaction::REASON_NURSE_PAYOUT,
                            "Partial payout for {$booking->completed_sessions} completed sessions — booking {$booking->reference_id}",
                            $booking->id
                        );
                    }
                }
            }

            // Nurse gets a cancellation strike
            NurseProfile::where('id', $nurseId)->increment('total_bookings_cancelled');

            // Auto-suspend if strikes exceed limit
            $nurse = NurseProfile::find($nurseId);
            $strikeLimit = (int) config('care.nurse_cancel_strike_limit', 3);

            if ($nurse && $nurse->total_bookings_cancelled >= $strikeLimit) {
                $nurse->markAsSuspended("Auto-suspended: {$nurse->total_bookings_cancelled} booking cancellations.");
            }

            return $this->performCancel($booking, Booking::CANCELLED_BY_NURSE, $reason, $refundAmount, $nursePayoutAmount);
        });
    }



    /**
     * Get refund percentage based on cancellation slab.
     */
    private function getRefundPercent(Booking $booking): int
    {
        // Find the next upcoming session to calculate hours before
        $nextSession = BookingSession::where('booking_id', $booking->id)
            ->where('status', BookingSession::STATUS_UPCOMING)
            ->orderBy('session_date')
            ->first();

        if (!$nextSession) {
            return 0;
        }

        $nextSessionStart = Carbon::parse(
            $nextSession->session_date->format('Y-m-d') . ' ' . ($nextSession->start_time ?? '00:00:00')
        );

        $hoursBefore = now()->diffInHours($nextSessionStart, false);

        // If session time is in the past, no refund
        if ($hoursBefore < 0) {
            return 0;
        }

        // Walk through slabs — first match wins (sorted highest hours first)
        $slabs = config('care.cancellation_slabs', []);

        foreach ($slabs as $slab) {
            if ($hoursBefore >= $slab['hours_before']) {
                return (int) $slab['refund_percent'];
            }
        }

        return 0;
    }

    /**
     * Perform the actual booking cancellation.
     */
    private function performCancel(
        Booking $booking,
        int $cancelledBy,
        ?string $reason,
        float $refundAmount,
        float $nursePayoutAmount,
    ): array {
        // Cancel all upcoming sessions
        BookingSession::where('booking_id', $booking->id)
            ->where('status', BookingSession::STATUS_UPCOMING)
            ->update(['status' => BookingSession::STATUS_CANCELLED]);

        // Determine payment status after cancellation
        $paymentStatus = $booking->payment_status;
        if ($refundAmount > 0) {
            $totalPaid = (float) $booking->total_amount;
            $paymentStatus = ($refundAmount >= $totalPaid)
                ? Booking::PAYMENT_REFUNDED
                : Booking::PAYMENT_PARTIALLY_REFUNDED;
        }

        $booking->update([
            'status' => Booking::STATUS_CANCELLED,
            'payment_status' => $paymentStatus,
            'cancelled_by' => $cancelledBy,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
            'refund_amount' => $refundAmount,
            'nurse_payout_amount' => $nursePayoutAmount,
        ]);

        // Reopen care request for re-bidding if nurse cancelled
        if ($cancelledBy === Booking::CANCELLED_BY_NURSE) {
            $booking->careRequest?->update([
                'status' => CareRequest::STATUS_MATCHING,
            ]);
        }

        $booking = $booking->fresh();

        if (isset($booking->user)) {
            $booking->user->notify(new \App\Notifications\BookingCancelledNotification($booking, 'patient'));
        }
        if (isset($booking->nurse->user)) {
            $booking->nurse->user->notify(new \App\Notifications\BookingCancelledNotification($booking, 'nurse'));
        }

        return [
            'booking' => $booking->fresh(),
            'refund_amount' => $refundAmount,
            'nurse_payout_amount' => $nursePayoutAmount,
            'cancelled_by' => $cancelledBy,
        ];
    }
}
