<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Banner;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/banners",
     *     operationId="getBanners",
     *     tags={"Banners"},
     *     summary="Get active banners",
     *     description="Retrieve all active banners",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Banners fetched successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Summer Sale"),
     *                 @OA\Property(property="type", type="integer", example=1),
     *                 @OA\Property(property="type_name", type="string", example="Category"),
     *                 @OA\Property(property="reference_id", type="integer", example=5),
     *                 @OA\Property(property="banner_image", type="string", format="url")
     *             ))
     *         )
     *     )
     * )
     * 
     * Get active banners
     */
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
