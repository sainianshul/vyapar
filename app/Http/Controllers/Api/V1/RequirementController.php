<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Requirement\StoreRequirementRequest;
use App\Http\Requests\Api\V1\Requirement\UpdateRequirementRequest;
use App\Models\Requirement;
use App\Services\RequirementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class RequirementController extends Controller
{
    protected RequirementService $requirementService;

    public function __construct(RequirementService $requirementService)
    {
        $this->requirementService = $requirementService;
    }

    #[OA\Get(
        path: '/api/v1/requirements',
        operationId: 'getRequirements',
        summary: 'Browse and search requirements',
        security: [['bearerAuth' => []]],
        tags: ['Requirements'],
        parameters: [
            new OA\Parameter(name: 'category_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'city', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'latitude', in: 'query', required: false, schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'longitude', in: 'query', required: false, schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'radius', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['latest', 'nearest'])),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Requirements fetched successfully'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Requirement::query()
            ->active()
            ->with(['images', 'user:id,name,profile_photo,city,created_at']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $query->where('search_tags', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('latitude') && $request->filled('longitude') && $request->filled('radius')) {
            $lat = (float) $request->latitude;
            $lng = (float) $request->longitude;
            $radius = (int) $request->radius;

            $query->selectRaw("*, (
                6371 * acos(
                    cos(radians(?)) * cos(radians(latitude))
                    * cos(radians(longitude) - radians(?))
                    + sin(radians(?)) * sin(radians(latitude))
                )
            ) AS distance", [$lat, $lng, $lat])
            ->having('distance', '<=', $radius);
        }

        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'nearest' => $request->filled('latitude') && $request->filled('longitude') 
                            ? $query->orderBy('distance', 'asc') 
                            : $query->orderByDesc('created_at'),
            default => $query->orderByDesc('created_at'),
        };

        $perPage = min($request->input('per_page', 20), 50);
        $requirements = $query->paginate($perPage);

        return ApiResponse::success('Requirements fetched', [
            'requirements' => $requirements->getCollection()->map(fn($r) => $r->toListArray()),
            'pagination' => [
                'current_page' => $requirements->currentPage(),
                'last_page' => $requirements->lastPage(),
                'per_page' => $requirements->perPage(),
                'total' => $requirements->total(),
            ],
        ]);
    }

    #[OA\Get(
        path: '/api/v1/requirements/{id}',
        operationId: 'getRequirementDetail',
        summary: 'Get requirement detail by id',
        security: [['bearerAuth' => []]],
        tags: ['Requirements'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Requirement detail fetched successfully'),
            new OA\Response(response: 404, description: 'Requirement not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $requirement = Requirement::where('id', $id)
            ->active()
            ->with(['user', 'category', 'images' => fn($q) => $q->orderBy('sort_order')])
            ->firstOrFail();

        return ApiResponse::success('Requirement detail', [
            'requirement' => $requirement->toDetailArray(),
        ]);
    }

    #[OA\Post(
        path: '/api/v1/requirements',
        operationId: 'storeRequirement',
        summary: 'Create a new requirement',
        security: [['bearerAuth' => []]],
        tags: ['Requirements'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['title'],
                    properties: [
                        new OA\Property(property: 'category_id', type: 'integer', nullable: true),
                        new OA\Property(property: 'title', type: 'string', example: 'Need 100 T-shirts'),
                        new OA\Property(property: 'description', type: 'string', nullable: true),
                        new OA\Property(property: 'quantity', type: 'integer', example: 100),
                        new OA\Property(property: 'target_budget', type: 'number', format: 'float', nullable: true, example: 5000),
                        new OA\Property(property: 'delivery_location', type: 'string', nullable: true),
                        new OA\Property(property: 'delivery_pincode', type: 'string', nullable: true),
                        new OA\Property(property: 'city', type: 'string', nullable: true),
                        new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true),
                        new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true),
                        new OA\Property(
                            property: 'images[]',
                            type: 'array',
                            items: new OA\Items(type: 'string', format: 'binary'),
                            nullable: true,
                            description: 'Up to 5 images'
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Requirement added successfully'),
            new OA\Response(response: 422, description: 'Validation Error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function store(StoreRequirementRequest $request): JsonResponse
    {
        $requirement = $this->requirementService->createRequirement(
            $request->validated(),
            $request->user(),
            $request->file('images')
        );

        return ApiResponse::success('Requirement added successfully', [
            'requirement' => $requirement->toDetailArray()
        ], 201);
    }

    #[OA\Get(
        path: '/api/v1/my/requirements',
        operationId: 'getMyRequirements',
        summary: 'Get own requirements',
        security: [['bearerAuth' => []]],
        tags: ['Requirements'],
        responses: [
            new OA\Response(response: 200, description: 'Requirements fetched successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function myRequirements(Request $request): JsonResponse
    {
        $requirements = Requirement::where('user_id', $request->user()->id)
            ->with('images')
            ->orderByDesc('created_at')
            ->paginate(20);

        return ApiResponse::success('My requirements', [
            'requirements' => $requirements->getCollection()->map(fn($r) => $r->toListArray()),
            'pagination' => [
                'current_page' => $requirements->currentPage(),
                'last_page' => $requirements->lastPage(),
                'per_page' => $requirements->perPage(),
                'total' => $requirements->total(),
            ],
        ]);
    }

    #[OA\Get(
        path: '/api/v1/my/requirements/{id}',
        operationId: 'getMyRequirementDetail',
        summary: 'Get requirement detail for logged-in buyer',
        security: [['bearerAuth' => []]],
        tags: ['Requirements'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Requirement detail fetched successfully'),
            new OA\Response(response: 404, description: 'Requirement not found'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function myRequirementDetail(Request $request, int $id): JsonResponse
    {
        $requirement = Requirement::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with(['category', 'images' => fn($q) => $q->orderBy('sort_order')])
            ->firstOrFail();

        return ApiResponse::success('Requirement detail', [
            'requirement' => $requirement->toDetailArray(),
        ]);
    }

    #[OA\Post(
        path: '/api/v1/my/requirements/{id}',
        operationId: 'updateRequirement',
        summary: 'Update a requirement (multipart/form-data)',
        security: [['bearerAuth' => []]],
        tags: ['Requirements'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'category_id', type: 'integer', nullable: true),
                        new OA\Property(property: 'title', type: 'string', nullable: true),
                        new OA\Property(property: 'description', type: 'string', nullable: true),
                        new OA\Property(property: 'quantity', type: 'integer', nullable: true),
                        new OA\Property(property: 'target_budget', type: 'number', format: 'float', nullable: true),
                        new OA\Property(property: 'delivery_location', type: 'string', nullable: true),
                        new OA\Property(property: 'delivery_pincode', type: 'string', nullable: true),
                        new OA\Property(property: 'city', type: 'string', nullable: true),
                        new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true),
                        new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true),
                        new OA\Property(
                            property: 'deleted_images[]',
                            type: 'array',
                            items: new OA\Items(type: 'integer'),
                            nullable: true,
                            description: 'IDs of images to delete'
                        ),
                        new OA\Property(
                            property: 'new_images[]',
                            type: 'array',
                            items: new OA\Items(type: 'string', format: 'binary'),
                            nullable: true,
                            description: 'New images'
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Requirement updated successfully'),
            new OA\Response(response: 422, description: 'Validation Error'),
            new OA\Response(response: 403, description: 'Forbidden (Not the owner)'),
            new OA\Response(response: 404, description: 'Requirement not found'),
        ]
    )]
    public function update(UpdateRequirementRequest $request, int $id): JsonResponse
    {
        $requirement = Requirement::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $updatedReq = $this->requirementService->updateRequirement(
            $requirement,
            $request->validated(),
            $request->file('new_images'),
            $request->input('deleted_images')
        );

        return ApiResponse::success('Requirement updated successfully', [
            'requirement' => $updatedReq->toDetailArray()
        ]);
    }

    #[OA\Delete(
        path: '/api/v1/my/requirements/{id}',
        operationId: 'deleteRequirement',
        summary: 'Delete a requirement',
        security: [['bearerAuth' => []]],
        tags: ['Requirements'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Requirement deleted successfully'),
            new OA\Response(response: 403, description: 'Forbidden (Not the owner)'),
            new OA\Response(response: 404, description: 'Requirement not found'),
        ]
    )]
    public function destroy(Request $request, int $id): JsonResponse
    {
        $requirement = Requirement::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        foreach ($requirement->images as $image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $requirement->delete();

        return ApiResponse::success('Requirement deleted successfully');
    }
}
