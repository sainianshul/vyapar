<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Feedback;
use Illuminate\Http\JsonResponse;

class FeedbackController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/feedbacks",
     *     operationId="getFeedbacks",
     *     tags={"Feedbacks"},
     *     summary="Get all feedbacks",
     *     description="Retrieve all feedbacks with user details",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Feedback fetched successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="feedback", type="string", example="Great service!"),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="user_name", type="string", example="John Doe"),
     *                 @OA\Property(property="user_city", type="string", example="New Delhi"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             ))
     *         )
     *     )
     * )
     * 
     * Get feedbacks
     */
    public function index(Request $request): JsonResponse
    {
        $feedbacks = Feedback::with('user:id,name,city')->latest()->get();

        $data = $feedbacks->map(function ($feedback) {
            return [
                'id' => $feedback->id,
                'user_id' => $feedback->user_id,
                'feedback' => $feedback->feedback,
                'status' => $feedback->status,
                'user_name' => $feedback->user ? $feedback->user->name : null,
                'user_city' => $feedback->user ? $feedback->user->city : null,
                'created_at' => $feedback->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Feedback fetched successfully',
            'data' => $data
        ]);
    }
}
