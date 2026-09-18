<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Banner;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    /**
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
