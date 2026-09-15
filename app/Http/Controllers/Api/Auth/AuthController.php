<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\SendOtpRequest;
use App\Http\Requests\Api\Auth\VerifyOtpRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    //Send OTP to mobile number.
    #[OA\Post(
        path: '/api/v1/auth/send-otp',
        operationId: 'sendOtp',
        summary: 'Send OTP',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['phone'],
                properties: [
                    new OA\Property(property: 'phone', type: 'string', example: '9876543210'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function sendOtp(SendOtpRequest $request)
    {
        $result = $this->authService->sendOtp(
            $request->string('phone')->value()
        );
        $message = 'OTP sent successfully';
        if (config('auth.show_test_otp')) {
            $message .= " ({$result['otp']})";
        }

        return ApiResponse::success(
            $message,
            [
                'is_registered' => $result['is_registered'],
            ]
        );
    }

    //Verify OTP and authenticate user.
    #[OA\Post(
        path: '/api/v1/auth/verify-otp',
        operationId: 'verifyOtp',
        summary: 'Verify OTP',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['phone', 'otp', 'role'],
                properties: [
                    new OA\Property(property: 'phone', type: 'string', example: '9876543210'),
                    new OA\Property(property: 'otp', type: 'string', example: '123456'),
                    new OA\Property(property: 'name', type: 'string', nullable: true, example: 'Harry'),
                    new OA\Property(property: 'role', type: 'integer', example: 2, description: '1 = User, 2 = Nurse'),
                    new OA\Property(property: 'fcm_token', type: 'string', nullable: true, example: 'fcm_xxxxxxxxx'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function verifyOtp(VerifyOtpRequest $request)
    {
        $result = $this->authService->verifyOtp(
            $request->validated(),
            $request->ip(),
            $request->userAgent()
        );

        return ApiResponse::success(
            'Authentication successful',
            [
                'token' => $result['token'],
                'user' => $result['user']->toUserResponse(),
            ]
        );
    }

    //Logout current user.
    #[OA\Post(
        path: '/api/v1/auth/logout',
        operationId: 'logout',
        summary: 'Logout',
        security: [['bearerAuth' => []]],
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: false
        ),
        responses: [
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function logout(Request $request)
    {
        $this->authService->logout(
            $request->user()
        );

        return ApiResponse::success(
            'Logged out successfully'
        );
    }

    //Get authenticated user.
    #[OA\Get(
        path: '/api/v1/auth/me',
        operationId: 'me',
        summary: 'Get authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function me(Request $request)
    {
        return ApiResponse::success(
            'Profile fetched successfully',
            [
                'user' => $request->user()
                    ->toUserResponse(),
            ]
        );
    }
}