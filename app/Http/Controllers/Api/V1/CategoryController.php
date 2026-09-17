<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CategoryController extends Controller
{
    #[OA\Get(
        path: '/api/v1/categories',
        operationId: 'listCategories',
        summary: 'Get all active categories (tree structure)',
        description: 'Returns all active root categories with their active children nested. Optionally pass parent_id to get subcategories of a specific category.',
        security: [['bearerAuth' => []]],
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(
                name: 'parent_id',
                in: 'query',
                required: false,
                description: 'Filter by parent category ID. Pass null or omit for root categories.',
                schema: new OA\Schema(type: 'integer', nullable: true)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function index(Request $request)
    {
        $query = Category::active()
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        } else {
            $query->root();
        }

        // Eager load active children (2 levels deep)
        $query->with([
            'children' => function ($q) {
                $q->active()->orderBy('sort_order')->orderBy('name')
                    ->with(['children' => function ($q2) {
                        $q2->active()->orderBy('sort_order')->orderBy('name');
                    }]);
            },
        ]);

        $categories = $query->get();

        return ApiResponse::success(
            'Categories fetched successfully',
            [
                'categories' => $categories->map->toApiResponse(),
            ]
        );
    }

    #[OA\Get(
        path: '/api/v1/categories/featured',
        operationId: 'featuredCategories',
        summary: 'Get featured categories for home page',
        description: 'Returns only active & featured categories, sorted by sort_order. Used on the app home screen.',
        security: [['bearerAuth' => []]],
        tags: ['Categories'],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function featured()
    {
        $categories = Category::active()
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return ApiResponse::success(
            'Featured categories fetched successfully',
            [
                'categories' => $categories->map->toApiResponse(),
            ]
        );
    }

    #[OA\Get(
        path: '/api/v1/categories/{id}',
        operationId: 'showCategory',
        summary: 'Get a single category by id',
        description: 'Returns category details along with its active subcategories.',
        security: [['bearerAuth' => []]],
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Category id',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 404, description: 'Category not found'),
        ]
    )]
    public function show(int $id)
    {
        $category = Category::active()
            ->where('id', $id)
            ->with([
                'children' => function ($q) {
                    $q->active()->orderBy('sort_order')->orderBy('name')
                        ->with(['children' => function ($q2) {
                            $q2->active()->orderBy('sort_order')->orderBy('name');
                        }]);
                },
            ])
            ->first();

        if (!$category) {
            return ApiResponse::error('Category not found', 404);
        }

        return ApiResponse::success(
            'Category fetched successfully',
            [
                'category' => $category->toApiResponse(),
            ]
        );
    }
}
