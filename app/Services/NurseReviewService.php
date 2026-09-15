<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Booking;
use App\Models\NurseProfile;
use App\Models\NurseReview;
use Illuminate\Support\Facades\DB;

class NurseReviewService
{
    /**
     * Submit a review for a nurse via a specific booking.
     *
     * @param int $userId
     * @param int $bookingId
     * @param array $data ['rating', 'review']
     * @return NurseReview
     * @throws ApiException
     */
    public function submitReview(int $userId, int $bookingId, array $data): NurseReview
    {
        return DB::transaction(function () use ($userId, $bookingId, $data) {
            $booking = Booking::with('nurse')->findOrFail($bookingId);

            // Verify the booking belongs to the user
            if ($booking->user_id !== $userId) {
                throw new ApiException('You are not authorized to review this booking.', 401);
            }

            // Verify the booking has a nurse
            if (!$booking->nurse_id) {
                throw new ApiException('This booking does not have a nurse assigned.', 400);
            }

            // Verify booking is not pending payment
            // "only user gave review who take booking with not pending payment"
            if ($booking->isPendingPayment()) {
                throw new ApiException('You cannot review a booking that is pending payment.', 400);
            }

            // Check if user already reviewed this booking
            $existingReview = NurseReview::where('user_id', $userId)
                ->where('booking_id', $bookingId)
                ->first();

            if ($existingReview) {
                throw new ApiException('You have already submitted a review for this booking.', 400);
            }

            // Create the review
            $review = NurseReview::create([
                'user_id' => $userId,
                'nurse_id' => $booking->nurse_id,
                'booking_id' => $bookingId,
                'rating' => $data['rating'],
                'review' => $data['review'] ?? null,
            ]);

            // Update Nurse Profile Average Rating & Total Reviews
            $this->updateNurseRating($booking->nurse_id);

            return $review;
        });
    }

    /**
     * Calculate and update the nurse's average rating and total reviews.
     *
     * @param int $nurseId
     */
    private function updateNurseRating(int $nurseId): void
    {
        $stats = NurseReview::where('nurse_id', $nurseId)
            ->selectRaw('COUNT(*) as total_reviews, AVG(rating) as avg_rating')
            ->first();

        NurseProfile::where('id', $nurseId)->update([
            'total_reviews' => $stats->total_reviews ?? 0,
            'avg_rating' => round($stats->avg_rating ?? 0, 2),
        ]);
    }
}
