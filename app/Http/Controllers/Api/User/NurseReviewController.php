<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\ApiException;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\StoreNurseReviewRequest;
use App\Services\NurseReviewService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class NurseReviewController extends Controller
{
    public function __construct(
        private NurseReviewService $reviewService
    ) {}

    #[OA\Post(
        path: '/api/v1/user/bookings/{booking_id}/review',
        operationId: 'storeNurseReview',
        summary: 'Submit a review and rating for a nurse after a booking',
        security: [['bearerAuth' => []]],
        tags: ['User - Bookings'],
        parameters: [
            new OA\Parameter(
                name: 'booking_id',
                description: 'The ID of the booking to review',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['rating'],
                properties: [
                    new OA\Property(property: 'rating', description: 'Rating from 1 to 5', type: 'integer', example: 5),
                    new OA\Property(property: 'review', description: 'Optional text review', type: 'string', example: 'Great service, highly recommended!')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Review submitted successfully'),
            new OA\Response(response: 400, description: 'Application Error'),
            new OA\Response(response: 422, description: 'Validation Error')
        ]
    )]
    public function store(StoreNurseReviewRequest $request, $bookingId): JsonResponse
    {
        try {
            $review = $this->reviewService->submitReview(
                $request->user()->id,
                $bookingId,
                $request->validated()
            );

            return ApiResponse::success('Review submitted successfully.', ['review' => $review]);
        } catch (ApiException $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            report($e);
            return ApiResponse::error('Failed to submit review.', 500);
        }
    }
}
