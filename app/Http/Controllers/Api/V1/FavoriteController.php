<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\Requirement;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class FavoriteController extends Controller
{
    #[OA\Post(
        path: '/api/v1/favorites/toggle',
        operationId: 'toggleFavorite',
        summary: 'Add or remove an item from wishlist/favorites',
        security: [['bearerAuth' => []]],
        tags: ['Favorites'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['type', 'id'],
                properties: [
                    new OA\Property(property: 'type', type: 'integer', description: '1: Product, 2: Requirement, 3: Seller', example: 1),
                    new OA\Property(property: 'id', type: 'integer', description: 'ID of the item', example: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 400, description: 'Invalid type or item not found'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|integer|in:1,2,3',
            'id' => 'required|integer',
        ]);

        $modelClass = Favorite::getModelClassForType($request->type);
        if (!$modelClass) {
            return ApiResponse::error('Invalid favorite type', 400);
        }

        // Verify if the item exists
        $itemExists = $modelClass::where('id', $request->id)->exists();
        if (!$itemExists) {
            return ApiResponse::error('Item not found', 404);
        }

        $user = $request->user();

        // Check if already favorited
        $favorite = Favorite::where('user_id', $user->id)
            ->where('model_type', $modelClass)
            ->where('model_id', $request->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return ApiResponse::success('Removed from wishlist');
        } else {
            Favorite::create([
                'user_id' => $user->id,
                'model_type' => $modelClass,
                'model_id' => $request->id,
            ]);
            return ApiResponse::success('Added to wishlist');
        }
    }

    #[OA\Get(
        path: '/api/v1/favorites',
        operationId: 'getFavorites',
        summary: 'List user favorites/wishlist',
        security: [['bearerAuth' => []]],
        tags: ['Favorites'],
        parameters: [
            new OA\Parameter(
                name: 'type',
                in: 'query',
                required: true,
                description: '1: Products, 2: Requirements, 3: Sellers',
                schema: new OA\Schema(type: 'integer', enum: [1, 2, 3])
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|integer|in:1,2,3',
        ]);

        $modelClass = Favorite::getModelClassForType($request->type);
        
        $favoritesQuery = Favorite::where('user_id', $request->user()->id)
            ->where('model_type', $modelClass)
            ->with('model')
            ->latest();

        $favorites = $favoritesQuery->get();

        // Format the output based on the requested type
        $formattedItems = $favorites->map(function ($fav) use ($request) {
            if (!$fav->model) return null;

            if ($request->type == Favorite::TYPE_PRODUCT) {
                return $fav->model->toBuyerListArray();
            } elseif ($request->type == Favorite::TYPE_REQUIREMENT) {
                return $fav->model->toBuyerListArray();
            } elseif ($request->type == Favorite::TYPE_SELLER) {
                return [
                    'id' => $fav->model->id,
                    'name' => $fav->model->name,
                    'city' => $fav->model->city,
                    'profile_photo' => $fav->model->profile_photo ? asset('storage/' . $fav->model->profile_photo) : null,
                ];
            }
            return null;
        })->filter()->values();

        return ApiResponse::success('Favorites fetched successfully', [
            'favorites' => $formattedItems
        ]);
    }
}
