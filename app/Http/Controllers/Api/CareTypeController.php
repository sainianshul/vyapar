<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\CareType;
use OpenApi\Attributes as OA;

class CareTypeController extends Controller
{
    //Get active care types list.
    #[OA\Get(
        path: '/api/v1/care-types',
        operationId: 'getCareTypes',
        summary: 'Get Care Types',
        security: [['bearerAuth' => []]],
        tags: ['Care Types'],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function index()
    {
        $careTypes = CareType::query()
            ->active()
            ->select([
                'id',
                'name',
                'slug',
                'description',
                'image_path',
            ])
            ->orderBy('name')
            ->get()
            ->map(fn($careType) => [

                'id' => $careType->id,

                'name' => $careType->name,

                'slug' => $careType->slug,

                'description' => $careType->description,

                'image_url' => $careType->image_path
                    ? asset('storage/' . $careType->image_path)
                    : null,
            ]);

        return ApiResponse::success('Care types fetched successfully', ['care_types' => $careTypes]);
    }
}