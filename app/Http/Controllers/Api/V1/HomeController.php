<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Feedback;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use OpenApi\Attributes as OA;

class HomeController extends Controller
{
    private const PRODUCT_LIMIT = 10;
    private const CATEGORY_LIMIT = 10;
    private const FEEDBACK_LIMIT = 5;
    private const CACHE_TTL = 300; // 5 minutes

    // Radius tiers in KM
    private const RADIUS_TIERS = [10, 50, 100];

    #[OA\Get(
        path: '/api/v1/home',
        operationId: 'getHome',
        summary: 'Buyer Home / Dashboard',
        description: 'Returns categories (10, featured first), nearby products (10, location-based), active banners, and recent feedbacks (5) in a single fast API call. Results are cached for performance.',
        security: [['bearerAuth' => []]],
        tags: ['Home']
    )]
    #[OA\Parameter(
        name: 'latitude',
        description: 'User latitude for nearby products',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'number', format: 'float')
    )]
    #[OA\Parameter(
        name: 'longitude',
        description: 'User longitude for nearby products',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'number', format: 'float')
    )]
    #[OA\Response(
        response: 200,
        description: 'Home data fetched successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Home data fetched successfully'),
                new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'banners', type: 'array', items: new OA\Items(properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'type', type: 'integer'),
                        new OA\Property(property: 'type_name', type: 'string'),
                        new OA\Property(property: 'reference_id', type: 'integer'),
                        new OA\Property(property: 'banner_image', type: 'string')
                    ])),
                    new OA\Property(property: 'categories', type: 'array', items: new OA\Items(properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'slug', type: 'string'),
                        new OA\Property(property: 'image', type: 'string'),
                        new OA\Property(property: 'is_featured', type: 'integer')
                    ])),
                    new OA\Property(property: 'products', type: 'array', items: new OA\Items(properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'title', type: 'string'),
                        new OA\Property(property: 'price', type: 'number'),
                        new OA\Property(property: 'city', type: 'string'),
                        new OA\Property(property: 'distance_km', type: 'number', nullable: true)
                    ])),
                    new OA\Property(property: 'products_source', type: 'string', example: 'nearby_10km'),
                    new OA\Property(property: 'feedbacks', type: 'array', items: new OA\Items(properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'feedback', type: 'string'),
                        new OA\Property(property: 'user_name', type: 'string'),
                        new OA\Property(property: 'user_city', type: 'string')
                    ]))
                ])
            ]
        )
    )]
    public function index(Request $request): JsonResponse
    {
        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        // Build a cache key based on a rounded lat/lng grid (~1km precision)
        // This way nearby users share the same cache
        $cacheKey = $this->buildCacheKey($lat, $lng);

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($lat, $lng) {
            return [
                'banners'    => $this->getBanners(),
                'categories' => $this->getCategories(),
                'products'   => $this->getProducts($lat, $lng),
                'feedbacks'  => $this->getFeedbacks(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Home data fetched successfully',
            'data'    => $data,
        ]);
    }

    /**
     * Build cache key with ~1km grid precision so nearby users share cache.
     */
    private function buildCacheKey(?string $lat, ?string $lng): string
    {
        if ($lat && $lng) {
            // Round to 2 decimal places (~1.1km precision)
            $gridLat = round((float) $lat, 2);
            $gridLng = round((float) $lng, 2);
            return "home:geo:{$gridLat}:{$gridLng}";
        }
        return 'home:global';
    }

    // ─── Banners ──────────────────────────────────

    private function getBanners(): array
    {
        return Banner::where('status', Banner::STATUS_ACTIVE)
            ->select('id', 'name', 'type', 'reference_id', 'image')
            ->get()
            ->map(fn(Banner $b) => [
                'id'           => $b->id,
                'name'         => $b->name,
                'type'         => $b->type,
                'type_name'    => $b->type_name,
                'reference_id' => $b->reference_id,
                'banner_image' => $b->image ? asset('storage/' . $b->image) : null,
            ])
            ->toArray();
    }

    // ─── Categories ───────────────────────────────

    private function getCategories(): array
    {
        // Featured first, then by sort_order — limit 10
        return Category::active()
            ->select('id', 'name', 'slug', 'image', 'icon', 'is_featured', 'sort_order')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(self::CATEGORY_LIMIT)
            ->get()
            ->map(fn(Category $c) => [
                'id'          => $c->id,
                'name'        => $c->name,
                'slug'        => $c->slug,
                'image'       => $c->image ? asset('storage/' . $c->image) : null,
                'icon'        => $c->icon,
                'is_featured' => $c->is_featured,
            ])
            ->toArray();
    }

    // ─── Products (location-based expanding radius) ───

    private function getProducts(?string $lat, ?string $lng): array
    {
        // If no location provided, return featured + latest
        if (!$lat || !$lng) {
            return [
                'items'  => $this->getFallbackProducts(),
                'source' => 'fallback',
            ];
        }

        $latitude  = (float) $lat;
        $longitude = (float) $lng;

        $nearbyProducts = collect();
        $source = '';

        // Try expanding radius: 10km → 50km → 100km
        foreach (self::RADIUS_TIERS as $radiusKm) {
            $products = $this->getProductsWithinRadius($latitude, $longitude, $radiusKm);
            if ($products->count() >= self::PRODUCT_LIMIT) {
                return [
                    'items'  => $products->take(self::PRODUCT_LIMIT)->values()->toArray(),
                    'source' => "nearby_{$radiusKm}km",
                ];
            }
            $nearbyProducts = $products;
            $source = "nearby_{$radiusKm}km";
        }

        // If we found some products but less than 10, fill the rest with fallback products
        $remaining = self::PRODUCT_LIMIT - $nearbyProducts->count();
        if ($remaining > 0) {
            $excludeIds = $nearbyProducts->pluck('id')->toArray();
            $fallback = $this->getFallbackProducts($remaining, $excludeIds);
            
            $items = array_merge($nearbyProducts->values()->toArray(), $fallback);
            
            return [
                'items'  => $items,
                'source' => $nearbyProducts->count() > 0 ? "nearby_and_fallback" : 'fallback',
            ];
        }

        return [
            'items'  => $nearbyProducts->values()->toArray(),
            'source' => $source,
        ];
    }

    /**
     * Get products within a given radius using Haversine formula.
     * Uses a bounding box pre-filter for index usage then exact Haversine.
     */
    private function getProductsWithinRadius(float $lat, float $lng, int $radiusKm)
    {
        // Bounding box pre-filter (rough, uses indexes)
        $latDelta = $radiusKm / 111.0;
        $lngDelta = $radiusKm / (111.0 * cos(deg2rad($lat)));

        $minLat = $lat - $latDelta;
        $maxLat = $lat + $latDelta;
        $minLng = $lng - $lngDelta;
        $maxLng = $lng + $lngDelta;

        return Product::active()
            ->select([
                'id', 'user_id', 'category_id', 'title', 'slug', 'price',
                'price_unit', 'is_negotiable', 'condition', 'city',
                'is_featured', 'is_verified', 'latitude', 'longitude',
                'created_at',
            ])
            ->with(['primaryImage:id,product_id,image_path,is_primary', 'seller:id,name,profile_photo,city'])
            ->whereBetween('latitude', [$minLat, $maxLat])
            ->whereBetween('longitude', [$minLng, $maxLng])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            // Exact Haversine distance
            ->addSelect(DB::raw(
                "(6371 * acos(
                    cos(radians({$lat}))
                    * cos(radians(latitude))
                    * cos(radians(longitude) - radians({$lng}))
                    + sin(radians({$lat}))
                    * sin(radians(latitude))
                )) AS distance_km"
            ))
            ->having('distance_km', '<=', $radiusKm)
            ->orderBy('distance_km')
            ->orderByDesc('is_featured')
            ->limit(self::PRODUCT_LIMIT)
            ->get()
            ->map(function (Product $p) {
                $arr = $p->toBuyerListArray();
                $arr['distance_km'] = $p->distance_km ? round((float) $p->distance_km, 1) : null;
                return $arr;
            });
    }

    /**
     * Fallback: featured products first, then latest products to fill limit.
     */
    private function getFallbackProducts(int $limit = self::PRODUCT_LIMIT, array $excludeIds = []): array
    {
        $featured = Product::active()
            ->featured()
            ->with(['primaryImage:id,product_id,image_path,is_primary', 'seller:id,name,profile_photo,city'])
            ->when(!empty($excludeIds), fn($q) => $q->whereNotIn('id', $excludeIds))
            ->latest()
            ->limit($limit)
            ->get();

        $remaining = $limit - $featured->count();

        if ($remaining > 0) {
            $excludeIds = array_merge($excludeIds, $featured->pluck('id')->toArray());
            $normal = Product::active()
                ->with(['primaryImage:id,product_id,image_path,is_primary', 'seller:id,name,profile_photo,city'])
                ->when(!empty($excludeIds), fn($q) => $q->whereNotIn('id', $excludeIds))
                ->latest()
                ->limit($remaining)
                ->get();
            $featured = $featured->merge($normal);
        }

        return $featured->map(fn(Product $p) => $p->toBuyerListArray())->values()->toArray();
    }

    // ─── Feedbacks ────────────────────────────────

    private function getFeedbacks(): array
    {
        return Feedback::where('status', Feedback::STATUS_ACTIVE)
            ->with('user:id,name,city')
            ->latest()
            ->limit(self::FEEDBACK_LIMIT)
            ->get()
            ->map(fn(Feedback $f) => [
                'id'        => $f->id,
                'feedback'  => $f->feedback,
                'user_name' => $f->user?->name,
                'user_city' => $f->user?->city,
                'created_at' => $f->created_at,
            ])
            ->toArray();
    }
}
