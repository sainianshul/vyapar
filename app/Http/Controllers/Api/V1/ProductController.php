<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\StoreProductRequest;
use App\Models\Product;
use App\Models\ProductView;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    #[OA\Get(
        path: '/api/v1/products',
        operationId: 'getProducts',
        summary: 'Browse and search products',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'category_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'condition', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'price_min', in: 'query', required: false, schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'price_max', in: 'query', required: false, schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'city', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'latitude', in: 'query', required: false, schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'longitude', in: 'query', required: false, schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'radius', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['latest', 'price_low', 'price_high', 'nearest'])),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Products fetched successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->active()
            ->with('primaryImage');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $query->where('search_tags', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
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
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'nearest' => $request->filled('latitude') && $request->filled('longitude') 
                            ? $query->orderBy('distance', 'asc') 
                            : $query->orderByDesc('created_at'),
            default => $query->orderByDesc('created_at'),
        };

        $perPage = min($request->input('per_page', 20), 50);
        $products = $query->paginate($perPage);

        return ApiResponse::success('Products fetched', [
            'products' => $products->getCollection()->map(fn($p) => $p->toBuyerListArray()),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    #[OA\Get(
        path: '/api/v1/products/featured',
        operationId: 'getFeaturedProducts',
        summary: 'Get featured products',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        responses: [
            new OA\Response(response: 200, description: 'Featured products fetched successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function featured(): JsonResponse
    {
        $products = Product::active()
            ->featured()
            ->with('primaryImage')
            ->orderByDesc('featured_at')
            ->paginate(20);

        return ApiResponse::success('Featured products', [
            'products' => $products->getCollection()->map(fn($p) => $p->toBuyerListArray()),
        ]);
    }

    #[OA\Get(
        path: '/api/v1/products/{id}',
        operationId: 'getProductDetail',
        summary: 'Get product detail by id',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Product detail fetched successfully'),
            new OA\Response(response: 404, description: 'Product not found'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function show(Request $request, int $id): JsonResponse
    {
        $product = Product::where('id', $id)
            ->active()
            ->with(['seller', 'category', 'images' => fn($q) => $q->orderBy('sort_order')])
            ->firstOrFail();

        $userId = $request->user()->id;
        
        $recentView = ProductView::where('product_id', $product->id)
            ->where('user_id', $userId)
            ->where('created_at', '>=', now()->subHour())
            ->exists();

        if (!$recentView) {
            ProductView::create([
                'product_id' => $product->id,
                'user_id' => $userId,
            ]);
            $product->increment('views_count');
        }

        return ApiResponse::success('Product detail', [
            'product' => $product->toBuyerDetailArray(),
        ]);
    }

    // Swagger annotation for store remains the same
    #[OA\Post(
        path: '/api/v1/products',
        operationId: 'storeProduct',
        summary: 'Create a new product',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['category_id', 'title', 'price', 'condition'],
                    properties: [
                        new OA\Property(property: 'category_id', type: 'integer', example: 1),
                        new OA\Property(property: 'title', type: 'string', example: 'iPhone 13 Pro'),
                        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Used for 1 year, in good condition'),
                        new OA\Property(property: 'price', type: 'number', format: 'float', example: 50000),
                        new OA\Property(property: 'price_unit', type: 'string', nullable: true, example: 'per piece', description: 'Allowed values: per piece, per dozen, per kg, per gram, per liter, per box, per pack'),
                        new OA\Property(property: 'is_negotiable', type: 'boolean', nullable: true, example: true),
                        new OA\Property(property: 'condition', type: 'integer', example: 1, description: 'Allowed values: 1 (New), 2 (Used)'),
                        new OA\Property(property: 'minimum_quantity', type: 'integer', nullable: true, example: 1),
                        new OA\Property(property: 'location', type: 'string', nullable: true, example: 'Connaught Place'),
                        new OA\Property(property: 'city', type: 'string', nullable: true, example: 'New Delhi'),
                        new OA\Property(property: 'pincode', type: 'string', nullable: true, example: '110001'),
                        new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true, example: 28.6304),
                        new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true, example: 77.2177),
                        new OA\Property(property: 'primary_image', type: 'string', format: 'binary', nullable: true, description: 'Primary product image (jpeg, png, jpg, webp)'),
                        new OA\Property(
                            property: 'additional_images[]',
                            type: 'array',
                            items: new OA\Items(type: 'string', format: 'binary'),
                            nullable: true,
                            description: 'Up to 5 additional images'
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Product added successfully'),
            new OA\Response(response: 422, description: 'Validation Error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 400, description: 'Service Exception Error'),
        ]
    )]
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->createProduct(
            $request->validated(),
            $request->user(),
            $request->file('primary_image'),
            $request->file('additional_images')
        );

        return ApiResponse::success('Product added successfully', [
            'product' => $product->toSellerDetailArray()
        ], 201);
    }

    #[OA\Get(
        path: '/api/v1/my/products',
        operationId: 'getMyProducts',
        summary: 'Get logged-in seller products',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['latest', 'oldest', 'most_viewed', 'most_leads'])),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Products fetched successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function myProducts(Request $request): JsonResponse
    {
        $query = Product::where('user_id', $request->user()->id)
            ->with('primaryImage');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('search_tags', 'LIKE', '%' . $request->search . '%');
        }

        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'oldest' => $query->orderBy('created_at', 'asc'),
            'most_viewed' => $query->orderByDesc('views_count'),
            'most_leads' => $query->orderByDesc('leads_count'),
            default => $query->orderByDesc('created_at'),
        };

        $products = $query->paginate(20);

        return ApiResponse::success('My products', [
            'products' => $products->getCollection()->map(fn($p) => $p->toSellerListArray()),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    #[OA\Get(
        path: '/api/v1/my/products/{id}',
        operationId: 'getMyProductDetail',
        summary: 'Get product detail for logged-in seller',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Product detail fetched successfully'),
            new OA\Response(response: 404, description: 'Product not found'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function myProductDetail(Request $request, int $id): JsonResponse
    {
        $product = Product::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with(['category', 'images' => fn($q) => $q->orderBy('sort_order')])
            ->firstOrFail();

        return ApiResponse::success('Product detail', [
            'product' => $product->toSellerDetailArray(),
        ]);
    }

    #[OA\Post(
        path: '/api/v1/products/{id}',
        operationId: 'updateProduct',
        summary: 'Update a product (Using POST for multipart/form-data with _method=PUT)',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: '_method', type: 'string', example: 'PUT', description: 'Required for Laravel to treat POST as PUT'),
                        new OA\Property(property: 'category_id', type: 'integer', nullable: true),
                        new OA\Property(property: 'title', type: 'string', nullable: true),
                        new OA\Property(property: 'description', type: 'string', nullable: true),
                        new OA\Property(property: 'price', type: 'number', format: 'float', nullable: true),
                        new OA\Property(property: 'price_unit', type: 'string', nullable: true),
                        new OA\Property(property: 'is_negotiable', type: 'boolean', nullable: true),
                        new OA\Property(property: 'condition', type: 'integer', nullable: true),
                        new OA\Property(property: 'minimum_quantity', type: 'integer', nullable: true),
                        new OA\Property(property: 'location', type: 'string', nullable: true),
                        new OA\Property(property: 'city', type: 'string', nullable: true),
                        new OA\Property(property: 'pincode', type: 'string', nullable: true),
                        new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true),
                        new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true),
                        new OA\Property(property: 'primary_image', type: 'string', format: 'binary', nullable: true),
                        new OA\Property(
                            property: 'deleted_images[]',
                            type: 'array',
                            items: new OA\Items(type: 'integer'),
                            nullable: true,
                            description: 'IDs of images to delete'
                        ),
                        new OA\Property(
                            property: 'additional_images[]',
                            type: 'array',
                            items: new OA\Items(type: 'string', format: 'binary'),
                            nullable: true,
                            description: 'New additional images'
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Product updated successfully'),
            new OA\Response(response: 422, description: 'Validation Error'),
            new OA\Response(response: 403, description: 'Forbidden (Not the owner)'),
            new OA\Response(response: 404, description: 'Product not found'),
        ]
    )]
    public function update(\App\Http\Requests\Api\V1\Product\UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = Product::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $updatedProduct = $this->productService->updateProduct(
            $product,
            $request->validated(),
            $request->file('primary_image'),
            $request->file('additional_images'),
            $request->input('deleted_images')
        );

        return ApiResponse::success('Product updated successfully', [
            'product' => $updatedProduct->toSellerDetailArray()
        ]);
    }
}
