<?php

namespace App\Http\Controllers\Api\Nurse;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Nurse\Onboarding\AvailabilityRequest;
use App\Http\Requests\Api\Nurse\Onboarding\BasicProfileRequest;
use App\Http\Requests\Api\Nurse\Onboarding\CareTypeRequest;
use App\Http\Requests\Api\Nurse\Onboarding\DocumentRequest;
use App\Http\Requests\Api\Nurse\Onboarding\EducationRequest;
use App\Http\Requests\Api\Nurse\Onboarding\WorkHistoryRequest;
use App\Models\NurseProfile;
use App\Services\OnboardingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class OnboardingController extends Controller
{

    public function __construct(
        private readonly OnboardingService $onboardingService
    ) {
    }

    //Save nurse basic profile.
    #[OA\Post(
        path: '/api/v1/nurse/onboarding/basic-profile',
        operationId: 'saveBasicProfile',
        summary: 'Save Basic Profile',
        security: [['bearerAuth' => []]],
        tags: ['Nurse Onboarding'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['email', 'years_of_experience', 'license_number', 'license_expiry_date', 'latitude', 'longitude', 'address', 'city', 'state', 'country', 'pincode'],
                    properties: [
                        new OA\Property(property: 'profile_photo', type: 'string', format: 'binary'),
                        new OA\Property(property: 'email', type: 'string', example: 'test@gmail.com'),
                        new OA\Property(property: 'bio', type: 'string', nullable: true, example: 'Experienced ICU nurse.'),
                        new OA\Property(property: 'years_of_experience', type: 'integer', example: 5),
                        new OA\Property(property: 'license_number', type: 'string', example: 'RN123456'),
                        new OA\Property(property: 'license_expiry_date', type: 'string', format: 'date', example: '2030-12-31'),
                        new OA\Property(property: 'latitude', type: 'number', format: 'float', example: 30.7333),
                        new OA\Property(property: 'longitude', type: 'number', format: 'float', example: 76.7794),
                        new OA\Property(property: 'address', type: 'string', example: 'Sector 17'),
                        new OA\Property(property: 'city', type: 'string', example: 'Chandigarh'),
                        new OA\Property(property: 'state', type: 'string', example: 'Punjab'),
                        new OA\Property(property: 'country', type: 'string', example: 'India'),
                        new OA\Property(property: 'pincode', type: 'string', example: '160017'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function saveBasicProfile(BasicProfileRequest $request)
    {
        $this->onboardingService->saveBasicProfile(
            $request->user(),
            $request->validated()
        );

        $nurseProfile = $request->user()
            ->nurseProfile
            ->fresh();

        return ApiResponse::success(
            message: 'Profile saved successfully.',
            data: [
                'onboarding' => $nurseProfile->getOnboardingResponse(),
            ]
        );
    }

    //Save nurse care types.
    #[OA\Post(
        path: '/api/v1/nurse/onboarding/care-type',
        operationId: 'saveCareTypes',
        summary: 'Save Care Types',
        security: [['bearerAuth' => []]],
        tags: ['Nurse Onboarding'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['care_type_ids'],
                properties: [
                    new OA\Property(
                        property: 'care_type_ids',
                        type: 'array',
                        items: new OA\Items(type: 'integer'),
                        example: [1, 2]
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function saveCareTypes(CareTypeRequest $request)
    {
        $this->onboardingService->saveCareTypes(
            $request->user(),
            $request->validated()
        );

        $nurseProfile = $request->user()
            ->nurseProfile
            ->fresh();

        return ApiResponse::success(
            message: 'Care types saved successfully.',
            data: [
                'onboarding' => $nurseProfile->getOnboardingResponse(),
            ]
        );
    }

    //Save nurse education details.
    #[OA\Post(
        path: '/api/v1/nurse/onboarding/education',
        operationId: 'saveEducation',
        summary: 'Save Education',
        security: [['bearerAuth' => []]],
        tags: ['Nurse Onboarding'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['educations'],
                properties: [
                    new OA\Property(
                        property: 'educations',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'degree_or_course', type: 'string', example: 'B.Sc Nursing'),
                                new OA\Property(property: 'institute_name', type: 'string', example: 'PGI Chandigarh'),
                                new OA\Property(property: 'field_of_study', type: 'string', example: 'Nursing'),
                                new OA\Property(property: 'start_year', type: 'integer', example: 2018),
                                new OA\Property(property: 'end_year', type: 'integer', nullable: true, example: 2022),
                                new OA\Property(property: 'is_currently_studying', type: 'boolean', example: false),
                            ]
                        )
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function saveEducation(EducationRequest $request)
    {
        $this->onboardingService->saveEducation(
            $request->user(),
            $request->validated()
        );

        $nurseProfile = $request->user()
            ->nurseProfile
            ->fresh();

        return ApiResponse::success(
            message: 'Education details saved successfully.',
            data: [
                'onboarding' => $nurseProfile->getOnboardingResponse(),
            ]
        );
    }

    //Save nurse work history.
    #[OA\Post(
        path: '/api/v1/nurse/onboarding/work-history',
        operationId: 'saveWorkHistory',
        summary: 'Save Work History',
        security: [['bearerAuth' => []]],
        tags: ['Nurse Onboarding'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['work_histories'],
                properties: [
                    new OA\Property(
                        property: 'work_histories',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'role_or_position', type: 'string', example: 'ICU Nurse'),
                                new OA\Property(property: 'organization_name', type: 'string', example: 'PGI Chandigarh'),
                                new OA\Property(property: 'location', type: 'string', example: 'Chandigarh'),
                                new OA\Property(property: 'start_date', type: 'string', format: 'date', example: '2020-01-01'),
                                new OA\Property(property: 'end_date', type: 'string', format: 'date', nullable: true, example: '2023-12-31'),
                                new OA\Property(property: 'is_currently_working', type: 'boolean', example: false),
                                new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Worked in ICU department.'),
                            ]
                        )
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function saveWorkHistory(WorkHistoryRequest $request): JsonResponse
    {
        $this->onboardingService->saveWorkHistory(
            $request->user(),
            $request->validated()
        );

        $nurseProfile = $request->user()
            ->nurseProfile
            ->fresh();

        return ApiResponse::success(
            message: 'Work history saved successfully.',
            data: [
                'onboarding' => $nurseProfile->getOnboardingResponse(),
            ]
        );
    }

    //Save nurse documents.
    #[OA\Post(
        path: '/api/v1/nurse/onboarding/documents',
        operationId: 'saveDocuments',
        summary: 'Save Documents',
        security: [['bearerAuth' => []]],
        tags: ['Nurse Onboarding'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'aadhar_document', type: 'string', format: 'binary', nullable: true, description: 'Aadhar document (jpg, jpeg, png, pdf, max 10MB)'),
                        new OA\Property(property: 'pan_document', type: 'string', format: 'binary', nullable: true, description: 'PAN document (jpg, jpeg, png, pdf, max 10MB)'),
                        new OA\Property(property: 'marksheet_10_document', type: 'string', format: 'binary', nullable: true, description: '10th marksheet document (jpg, jpeg, png, pdf, max 10MB)'),
                        new OA\Property(property: 'marksheet_12_document', type: 'string', format: 'binary', nullable: true, description: '12th marksheet document (jpg, jpeg, png, pdf, max 10MB)'),
                        new OA\Property(property: 'nursing_certificate_document', type: 'string', format: 'binary', nullable: true, description: 'Nursing certificate document (jpg, jpeg, png, pdf, max 10MB)'),
                        new OA\Property(property: 'license_document', type: 'string', format: 'binary', nullable: true, description: 'License document (jpg, jpeg, png, pdf, max 10MB)'),
                        new OA\Property(property: 'degree_document', type: 'string', format: 'binary', nullable: true, description: 'Degree document (jpg, jpeg, png, pdf, max 10MB)'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function saveDocuments(DocumentRequest $request)
    {
        $this->onboardingService->saveDocuments(
            $request->user(),
            $request->validated()
        );

        $nurseProfile = $request->user()
            ->nurseProfile
            ->fresh();

        return ApiResponse::success(
            message: 'Documents uploaded successfully.',
            data: [
                'onboarding' => $nurseProfile->getOnboardingResponse(),
            ]
        );
    }

    //Submit profile for review.
    #[OA\Post(
        path: '/api/v1/nurse/onboarding/submit',
        operationId: 'submitForReview',
        summary: 'Submit For Review',
        security: [['bearerAuth' => []]],
        tags: ['Nurse Onboarding'],
        requestBody: new OA\RequestBody(required: false),
        responses: [
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function submitForReview(Request $request)
    {
        $user = $request->user();
        $nurseProfile = $user->nurseProfile;

        $this->onboardingService->submitForReview(
            $user
        );

        \App\Helpers\ActivityLogger::log(
            \App\Models\Activity::ACTION_ONBOARDING_SUBMIT,
            'Nurse submitted onboarding application for review.',
            $nurseProfile
        );

        return ApiResponse::success(
            message: 'Profile submitted for review successfully.',
            data: [
                'onboarding' => ['is_completed' => true],
                'profile_status' => NurseProfile::STATUS_UNDER_REVIEW,
                'profile_status_name' => NurseProfile::getStatusList()[NurseProfile::STATUS_UNDER_REVIEW],
            ]
        );
    }

    //Reapply onboarding profile.
    #[OA\Post(
        path: '/api/v1/nurse/onboarding/reapply',
        operationId: 'reapply',
        summary: 'Reapply',
        security: [['bearerAuth' => []]],
        tags: ['Nurse Onboarding'],
        requestBody: new OA\RequestBody(required: false),
        responses: [
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function reapply(Request $request)
    {
        $this->onboardingService->reapply(
            $request->user()
        );

        $nurseProfile = $request->user()
            ->nurseProfile
            ->fresh();

        return ApiResponse::success(
            message: 'Profile reapplication started successfully.',
            data: [
                'onboarding' => $nurseProfile->getOnboardingResponse(),
                'profile_status' => NurseProfile::STATUS_UNDER_REVIEW,
                'profile_status_name' => NurseProfile::getStatusList()[NurseProfile::STATUS_UNDER_REVIEW],
            ]
        );
    }

    //Get onboarding step data.
    #[OA\Get(
        path: '/api/v1/nurse/onboarding/step-data/{step}',
        operationId: 'getStepData',
        summary: 'Get Step Data',
        security: [['bearerAuth' => []]],
        tags: ['Nurse Onboarding'],
        parameters: [
            new OA\Parameter(
                name: 'step',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 3)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function getStepData(Request $request, int $step)
    {
        $data = $this->onboardingService->getStepData(
            $request->user(),
            $step
        );

        return ApiResponse::success(
            message: 'Step data fetched successfully.',
            data: $data
        );
    }
}