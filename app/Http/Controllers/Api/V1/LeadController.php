<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;
use Exception;

class LeadController extends Controller
{
    protected LeadService $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    #[OA\Post(
        path: '/api/v1/leads',
        operationId: 'captureLead',
        summary: 'Capture a lead (inquiry, call, etc)',
        security: [['bearerAuth' => []]],
        tags: ['Leads'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['seller_id', 'source'],
                    properties: [
                        new OA\Property(property: 'seller_id', type: 'integer', example: 2),
                        new OA\Property(property: 'product_id', type: 'integer', nullable: true, example: 1),
                        new OA\Property(property: 'requirement_id', type: 'integer', nullable: true, example: null),
                        new OA\Property(property: 'source', type: 'integer', example: 1, description: '1:Inquiry Form, 2:Call, 3:Chat, 4:View Number'),
                        new OA\Property(property: 'quantity', type: 'integer', nullable: true, example: 500),
                        new OA\Property(property: 'message', type: 'string', nullable: true, example: 'Need best price'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Lead captured successfully'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'seller_id' => 'required|integer|exists:users,id',
            'product_id' => 'nullable|integer|exists:products,id',
            'requirement_id' => 'nullable|integer|exists:requirements,id',
            'source' => ['required', 'integer', Rule::in(array_keys(Lead::getSourceList()))],
            'quantity' => 'nullable|integer|min:1',
            'message' => 'nullable|string',
        ]);

        try {
            $data = $request->only(['seller_id', 'product_id', 'requirement_id', 'source', 'quantity', 'message']);
            $data['buyer_id'] = $request->user()->id;

            $lead = $this->leadService->captureLead($data);
            $lead->load(['buyer:id,name,profile_photo,city,phone', 'product:id,title', 'requirement:id,title']);

            return ApiResponse::success('Lead captured successfully', [
                'lead' => $lead->toApiResponse()
            ], 201);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    #[OA\Get(
        path: '/api/v1/my/leads/received',
        operationId: 'myReceivedLeads',
        summary: 'Get leads received by the logged-in user (seller)',
        security: [['bearerAuth' => []]],
        tags: ['Leads'],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'temperature', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Received leads fetched successfully'),
        ]
    )]
    public function myReceivedLeads(Request $request): JsonResponse
    {
        $query = Lead::where('seller_id', $request->user()->id)
            ->with(['buyer:id,name,profile_photo,city,phone', 'product:id,title', 'requirement:id,title'])
            ->orderByDesc('updated_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('temperature')) {
            $query->where('temperature', $request->temperature);
        }

        $leads = $query->paginate(20);

        return ApiResponse::success('Received leads fetched successfully', [
            'leads' => $leads->getCollection()->map(fn($l) => $l->toApiResponse()),
            'pagination' => [
                'current_page' => $leads->currentPage(),
                'last_page' => $leads->lastPage(),
                'total' => $leads->total(),
            ]
        ]);
    }

    #[OA\Get(
        path: '/api/v1/my/leads/sent',
        operationId: 'mySentLeads',
        summary: 'Get leads/inquiries sent by the logged-in user (buyer)',
        security: [['bearerAuth' => []]],
        tags: ['Leads'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Sent leads fetched successfully'),
        ]
    )]
    public function mySentLeads(Request $request): JsonResponse
    {
        $leads = Lead::where('buyer_id', $request->user()->id)
            ->with(['seller:id,name,profile_photo,city,phone', 'product:id,title', 'requirement:id,title'])
            ->orderByDesc('updated_at')
            ->paginate(20);

        return ApiResponse::success('Sent leads fetched successfully', [
            'leads' => $leads->getCollection()->map(fn($l) => $l->toApiResponse()),
            'pagination' => [
                'current_page' => $leads->currentPage(),
                'last_page' => $leads->lastPage(),
                'total' => $leads->total(),
            ]
        ]);
    }

    #[OA\Put(
        path: '/api/v1/my/leads/{id}/status',
        operationId: 'updateLeadStatus',
        summary: 'Update lead status (for seller to manage leads)',
        security: [['bearerAuth' => []]],
        tags: ['Leads'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['status'],
                    properties: [
                        new OA\Property(property: 'status', type: 'integer', description: '1:New, 2:Contacted, 3:Converted, 4:Rejected'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Lead status updated successfully'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'integer', Rule::in(array_keys(Lead::getStatusList()))]
        ]);

        $lead = Lead::where('id', $id)
            ->where('seller_id', $request->user()->id)
            ->firstOrFail();

        $lead->update(['status' => $request->status]);

        return ApiResponse::success('Lead status updated successfully', [
            'lead' => $lead->toApiResponse()
        ]);
    }
}
