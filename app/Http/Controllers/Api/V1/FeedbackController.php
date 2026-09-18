<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Feedback;
use Illuminate\Http\JsonResponse;

class FeedbackController extends Controller
{
    /**
     * Get feedbacks
     */
    public function index(Request $request): JsonResponse
    {
        $feedbacks = Feedback::latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Feedback fetched successfully',
            'data' => $feedbacks
        ]);
    }
}
