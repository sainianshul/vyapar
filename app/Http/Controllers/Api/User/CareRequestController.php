<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\CreateCareRequest;
use App\Http\Requests\Api\User\UpdateCareRequest;
use App\Models\CareRequest;
use App\Services\BiddingService;
use App\Services\CareRequestService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CareRequestController extends Controller
{
    protected CareRequestService $careRequestService;
    protected BiddingService $biddingService;

    public function __construct(CareRequestService $careRequestService, BiddingService $biddingService)
    {
        $this->careRequestService = $careRequestService;
        $this->biddingService = $biddingService;
    }

    #[OA\Get(
        path: '/api/v1/user/care-requests',
        operationId: 'listUserCareRequests',
        summary: 'List user care requests',
        description: 'Get a paginated list of care requests created by the authenticated user.',
        security: [['bearerAuth' => []]],
        tags: ['User Care Requests'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of care requests',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Care requests retrieved successfully.'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            )
        ]
    )]
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->isUser()) {
            return ApiResponse::error('Only patients can view their care requests.', 403);
        }

        $careRequests = $this->careRequestService->listForUser($user->id);

        $careRequests->getCollection()->transform(function ($careRequest) {
            return $careRequest->toUserApiArray();
        });

        return ApiResponse::success(
            'Care requests retrieved successfully.',
            ['care_requests' => $careRequests],
            200
        );
    }

    #[OA\Post(
        path: '/api/v1/user/care-requests',
        operationId: 'storeCareRequest',
        summary: 'Create a new care request',
        description: 'Creates a new care request for a patient.',
        security: [['bearerAuth' => []]],
        tags: ['User Care Requests'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['care_type_id', 'care_for', 'contact_phone', 'latitude', 'longitude', 'address', 'city', 'state', 'pincode', 'start_date'],
                properties: [
                    new OA\Property(property: 'care_type_id', type: 'integer', example: 1),
                    new OA\Property(property: 'care_for', type: 'integer', description: '1: Self, 2: Other', enum: [\App\Models\CareRequest::CARE_FOR_SELF, \App\Models\CareRequest::CARE_FOR_OTHER], example: \App\Models\CareRequest::CARE_FOR_SELF),
                    new OA\Property(property: 'patient_name', type: 'string', description: 'Required if care_for is 2', example: 'John Doe'),
                    new OA\Property(property: 'patient_age', type: 'string', description: 'Required if care_for is 2', example: '65'),
                    new OA\Property(property: 'contact_phone', type: 'string', example: '1234567890'),
                    new OA\Property(property: 'secondary_phone', type: 'string', example: '0987654321'),
                    new OA\Property(property: 'latitude', type: 'number', format: 'float', example: 28.6139),
                    new OA\Property(property: 'longitude', type: 'number', format: 'float', example: 77.2090),
                    new OA\Property(property: 'address', type: 'string', example: '123 Main St'),
                    new OA\Property(property: 'city', type: 'string', example: 'New Delhi'),
                    new OA\Property(property: 'state', type: 'string', example: 'Delhi'),
                    new OA\Property(property: 'country', type: 'string', example: 'India'),
                    new OA\Property(property: 'pincode', type: 'string', example: '110001'),
                    new OA\Property(property: 'start_date', type: 'string', format: 'date', example: '2026-06-01'),
                    new OA\Property(property: 'end_date', type: 'string', format: 'date', example: '2026-06-05'),
                    new OA\Property(property: 'start_time', type: 'string', example: '09:00'),
                    new OA\Property(property: 'end_time', type: 'string', example: '17:00'),
                    new OA\Property(property: 'notes', type: 'string', example: 'Patient needs post-surgery care'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Care request created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Care request created successfully.'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            )
        ]
    )]
    public function store(CreateCareRequest $request)
    {
        $user = $request->user();

        if (!$user->isUser()) {
            return ApiResponse::error('Only patients can create care requests.', 403);
        }

        $result = $this->careRequestService->createAndMatch($request->validated(), $user->id);

        if (isset($result['care_request']) && $result['care_request'] instanceof \App\Models\CareRequest) {
            $result['care_request'] = $result['care_request']->toUserApiArray();
        }

        return ApiResponse::success(
            'Care request created successfully.',
            $result,
            201
        );
    }

    #[OA\Get(
        path: '/api/v1/user/care-requests/{id}',
        operationId: 'showCareRequest',
        summary: 'View care request details',
        description: 'Get details of a specific care request.',
        security: [['bearerAuth' => []]],
        tags: ['User Care Requests'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Care request retrieved successfully'
            )
        ]
    )]
    public function show(Request $request, int $id)
    {
        $user = $request->user();

        if (!$user->isUser()) {
            return ApiResponse::error('Only patients can view care requests.', 403);
        }

        $careRequest = CareRequest::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$careRequest) {
            return ApiResponse::error('Care request not found.', 404);
        }

        return ApiResponse::success(
            'Care request retrieved successfully.',
            ['care_request' => $careRequest->toUserApiArray()],
            200
        );
    }

    #[OA\Put(
        path: '/api/v1/user/care-requests/{id}',
        operationId: 'updateCareRequest',
        summary: 'Update a care request',
        description: 'Updates a care request. If matching, only notes/phone allowed. If failed, full edit allowed and matching restarts.',
        security: [['bearerAuth' => []]],
        tags: ['User Care Requests'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'notes', type: 'string'),
                    new OA\Property(property: 'start_date', type: 'string', format: 'date'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Care request updated successfully'
            )
        ]
    )]
    public function update(UpdateCareRequest $request, int $id)
    {
        $user = $request->user();

        if (!$user->isUser()) {
            return ApiResponse::error('Only patients can update care requests.', 403);
        }

        $result = $this->careRequestService->updateRequest($id, $request->validated(), $user->id);

        if (isset($result['care_request']) && $result['care_request'] instanceof \App\Models\CareRequest) {
            $result['care_request'] = $result['care_request']->toUserApiArray();
        }

        return ApiResponse::success(
            $result['message'] ?? 'Care request updated successfully.',
            $result,
            200
        );
    }

    #[OA\Post(
        path: '/api/v1/user/care-requests/{id}/cancel',
        operationId: 'cancelCareRequest',
        summary: 'Cancel a care request',
        description: 'Cancels a care request and clears all nurse matching caches/bids.',
        security: [['bearerAuth' => []]],
        tags: ['User Care Requests'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'cancel_reason', type: 'string')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Care request cancelled successfully'
            )
        ]
    )]
    public function cancel(Request $request, int $id)
    {
        $user = $request->user();

        if (!$user->isUser()) {
            return ApiResponse::error('Only patients can cancel care requests.', 403);
        }

        $reason = $request->input('cancel_reason');

        $careRequest = $this->careRequestService->cancelRequest($id, $user->id, $reason);

        return ApiResponse::success(
            'Care request cancelled successfully.',
            ['care_request' => $careRequest->toUserApiArray()],
            200
        );
    }

    #[OA\Get(
        path: '/api/v1/user/care-requests/{care_request_id}/bids',
        operationId: 'listBidsForCareRequest',
        summary: 'View bids on a care request',
        description: 'User views all active bids placed by nurses on their care request. Shows limited nurse info.',
        security: [['bearerAuth' => []]],
        tags: ['User Care Requests'],
        parameters: [
            new OA\Parameter(
                name: 'care_request_id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Bids retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Bids retrieved successfully.'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            )
        ]
    )]
    public function bids(Request $request, int $careRequestId)
    {
        $user = $request->user();

        if (!$user->isUser()) {
            return ApiResponse::error('Only patients can view bids.', 403);
        }

        $result = $this->biddingService->getBidsForUser($careRequestId, $user->id);

        return ApiResponse::success('Bids retrieved successfully.', $result, 200);
    }

    #[OA\Get(
        path: '/api/v1/user/care-requests/{care_request_id}/bids/{bid_id}',
        operationId: 'showBidForCareRequest',
        summary: 'View a single bid on a care request',
        description: 'User views details of a single bid. Shows limited nurse info plus experience.',
        security: [['bearerAuth' => []]],
        tags: ['User Care Requests'],
        parameters: [
            new OA\Parameter(name: 'care_request_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'bid_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Bid retrieved successfully',
            )
        ]
    )]
    public function showBid(Request $request, int $careRequestId, int $bidId)
    {
        $user = $request->user();

        if (!$user->isUser()) {
            return ApiResponse::error('Only patients can view bids.', 403);
        }

        $result = $this->biddingService->getBidForUser($careRequestId, $bidId, $user->id);

        return ApiResponse::success('Bid retrieved successfully.', $result, 200);
    }
}
