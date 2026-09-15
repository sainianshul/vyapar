<?php

use App\Http\Controllers\Api\CareTypeController;
use App\Http\Controllers\Api\Nurse\CareRequestController as NurseCareRequestController;
use App\Http\Controllers\Api\Nurse\BookingController as NurseBookingController;
use App\Http\Controllers\Api\Nurse\OnboardingController;
use App\Http\Controllers\Api\User\CareRequestController as UserCareRequestController;
use App\Http\Controllers\Api\User\BookingController as UserBookingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;


Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API is running',
    ]);
});

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {

        Route::post('send-otp', [AuthController::class, 'sendOtp']);

        Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    });


    Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('auth')->group(function () {

            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
        });

        Route::get('care-types', [CareTypeController::class, 'index']);

        // User Routes
        Route::prefix('user')->group(function () {

            // Care Requests
            Route::get('care-requests', [UserCareRequestController::class, 'index']);
            Route::post('care-requests', [UserCareRequestController::class, 'store']);
            Route::get('care-requests/{id}', [UserCareRequestController::class, 'show']);
            Route::put('care-requests/{id}', [UserCareRequestController::class, 'update']);
            Route::post('care-requests/{care_request_id}/cancel', [UserCareRequestController::class, 'cancel']);
            Route::get('care-requests/{care_request_id}/bids', [UserCareRequestController::class, 'bids']);
            Route::get('care-requests/{care_request_id}/bids/{bid_id}', [UserCareRequestController::class, 'showBid']);

            // Bookings
            Route::get('bookings', [UserBookingController::class, 'index']);
            Route::get('bookings/{booking_id}', [UserBookingController::class, 'show']);
            Route::post('bookings/select-bid', [UserBookingController::class, 'selectBid']);
            Route::post('bookings/{booking_id}/pay', [UserBookingController::class, 'initiatePayment']);
            Route::post('bookings/{booking_id}/confirm-payment', [UserBookingController::class, 'confirmPayment']);
            Route::post('bookings/{booking_id}/cancel', [UserBookingController::class, 'cancel']);
            Route::get('sessions/today', [UserBookingController::class, 'todaySessions']);
            Route::get('bookings/{booking_id}/sessions', [UserBookingController::class, 'listSessions']);
            Route::get('bookings/{booking_id}/sessions/{session_id}', [UserBookingController::class, 'showSession']);
            Route::post('bookings/{booking_id}/review', [\App\Http\Controllers\Api\User\NurseReviewController::class, 'store']);

        });

        // Nurse Routes
        Route::prefix('nurse')->group(function () {

            // Protected routes that require an approved nurse profile
            Route::middleware(['nurse_approved'])->group(function () {

                // Profile
                Route::post('profile/update', [\App\Http\Controllers\Api\Nurse\ProfileController::class, 'updateProfile']);
                Route::post('profile/toggle-availability', [\App\Http\Controllers\Api\Nurse\ProfileController::class, 'toggleAvailability']);

                // Care Requests (bidding)
                Route::get('care-requests', [NurseCareRequestController::class, 'index']);
                Route::get('care-requests/my-bids', [NurseCareRequestController::class, 'myBids']);
                Route::get('care-requests/{id}', [NurseCareRequestController::class, 'show']);
                Route::post('care-requests/bid', [NurseCareRequestController::class, 'placeBid']);

                // Bookings
                Route::get('bookings', [NurseBookingController::class, 'index']);
                Route::get('schedule', [NurseBookingController::class, 'schedule']);
                Route::post('sessions/{session_id}/start', [NurseBookingController::class, 'startSession']);
                Route::post('sessions/{session_id}/end', [NurseBookingController::class, 'endSession']);
                Route::post('sessions/{session_id}/force-end', [NurseBookingController::class, 'forceEndSession']);
                Route::post('bookings/{booking_id}/cancel', [NurseBookingController::class, 'cancel']);
                Route::get('sessions/today', [NurseBookingController::class, 'todaySessions']);
                Route::get('bookings/{booking_id}/sessions', [NurseBookingController::class, 'listSessions']);
                Route::get('bookings/{booking_id}/sessions/{session_id}', [NurseBookingController::class, 'showSession']);

                // Wallet & Withdrawals
                Route::get('wallet', [NurseBookingController::class, 'wallet']);
                Route::post('wallet/withdraw', [NurseBookingController::class, 'requestWithdrawal']);
                Route::get('wallet/withdrawals', [NurseBookingController::class, 'withdrawals']);
            });

            //  Nurse Onboarding Routes
            Route::post('onboarding/basic-profile', [OnboardingController::class, 'saveBasicProfile']);
            Route::post('onboarding/care-type', [OnboardingController::class, 'saveCareTypes']);
            Route::post('onboarding/education', [OnboardingController::class, 'saveEducation']);
            Route::post('onboarding/work-history', [OnboardingController::class, 'saveWorkHistory']);
            Route::post('onboarding/documents', [OnboardingController::class, 'saveDocuments']);
            Route::post('onboarding/submit', [OnboardingController::class, 'submitForReview']);
            Route::get('onboarding/step-data/{step}', [OnboardingController::class, 'getStepData']);
            Route::post('onboarding/reapply', [OnboardingController::class, 'reapply']);
        });

        // Support Routes For Both 
        Route::prefix('support')->group(function () {
            Route::get('categories', [\App\Http\Controllers\Api\SupportController::class, 'categories']);
            Route::get('tickets', [\App\Http\Controllers\Api\SupportController::class, 'index']);
            Route::post('tickets', [\App\Http\Controllers\Api\SupportController::class, 'store']);
            Route::get('tickets/{id}', [\App\Http\Controllers\Api\SupportController::class, 'show']);
            Route::post('tickets/{id}/reply', [\App\Http\Controllers\Api\SupportController::class, 'reply']);
            Route::get('tickets/{id}/messages', [\App\Http\Controllers\Api\SupportController::class, 'messages']);
            Route::post('tickets/{id}/read', [\App\Http\Controllers\Api\SupportController::class, 'markRead']);
        });

    });
});