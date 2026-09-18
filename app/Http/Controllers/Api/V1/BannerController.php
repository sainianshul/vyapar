<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Banner;
use Illuminate\Http\JsonResponse;

use OpenApi\Attributes as OA;

class BannerController extends Controller
{
    #[OA\Get(
        path: '/api/v1/banners',
        operationId: 'getBanners',
        summary: 'Get active banners',
        description: 'Retrieve all active banners',
        tags: ['Banners']
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful operation',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Banners fetched successfully'),
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'Summer Sale'),
                    new OA\Property(property: 'type', type: 'integer', example: 1),
                    new OA\Property(property: 'type_name', type: 'string', example: 'Category'),
                    new OA\Property(property: 'reference_id', type: 'integer', example: 5),
                    new OA\Property(property: 'banner_image', type: 'string', format: 'url')
                ]))
            ]
        )
    )]
    public function index(Request $request): JsonResponse
    {
        $banners = Banner::where('status', Banner::STATUS_ACTIVE)->get();

        $data = $banners->map(function ($banner) {
            return [
                'id' => $banner->id,
                'name' => $banner->name,
                'type' => $banner->type,
                'type_name' => $banner->type_name,
                'reference_id' => $banner->reference_id,
                'banner_image' => $banner->banner_image,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Banners fetched successfully',
            'data' => $data
        ]);
    }
}
