<?php

namespace App\Http\Controllers\Api\Nurse;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Nurse\Profile\ToggleAvailabilityRequest;
use App\Http\Requests\Api\Nurse\Profile\UpdateProfileRequest;
use App\Services\Nurse\ProfileService;
use OpenApi\Attributes as OA;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService
    ) {
    }

    #[OA\Post(
        path: '/api/v1/nurse/profile/update',
        operationId: 'nurseUpdateProfile',
        summary: 'Update Nurse Profile (Post-Onboarding)',
        security: [['bearerAuth' => []]],
        tags: ['Nurse Profile'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['available_from', 'available_to', 'available_days'],
                properties: [
                    new OA\Property(property: 'bio', type: 'string', nullable: true, example: 'Experienced ICU nurse.'),
                    new OA\Property(property: 'available_from', type: 'string', example: '09:00'),
                    new OA\Property(property: 'available_to', type: 'string', example: '18:00'),
                    new OA\Property(
                        property: 'available_days',
                        type: 'array',
                        items: new OA\Items(type: 'integer'),
                        example: [1, 2, 5]
                    ),
                    new OA\Property(property: 'timezone', type: 'string', nullable: true, example: 'Asia/Kolkata'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 400, description: 'Validation Error'),
            new OA\Response(response: 403, description: 'Forbidden (Not Approved)')
        ]
    )]
    public function updateProfile(UpdateProfileRequest $request)
    {
        $nurseProfile = $this->profileService->updateProfile(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::success(
            message: 'Profile updated successfully.',
            data: $nurseProfile->toApiArray()
        );
    }

    #[OA\Post(
        path: '/api/v1/nurse/profile/toggle-availability',
        operationId: 'nurseToggleAvailability',
        summary: 'Toggle Nurse Availability',
        security: [['bearerAuth' => []]],
        tags: ['Nurse Profile'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['is_available'],
                properties: [
                    new OA\Property(property: 'is_available', type: 'boolean', example: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 403, description: 'Forbidden (Not Approved)')
        ]
    )]
    public function toggleAvailability(ToggleAvailabilityRequest $request)
    {
        $nurseProfile = $this->profileService->toggleAvailability(
            $request->user(),
            $request->validated('is_available')
        );

        return ApiResponse::success(
            message: 'Availability status updated successfully.',
            data: $nurseProfile->toApiArray()
        );
    }
}
