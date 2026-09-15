<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "VCanCare API",
    version: "1.0.0",
    description: "VCanCare Backend APIs"
)]
#[OA\Server(
    url: "http://localhost:8000",
    description: "Local Server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
#[OA\Get(
    path: "/api/health",
    summary: "Health Check",
    description: "Returns API health status",
    tags: ["General"],
    responses: [
        new OA\Response(
            response: 200,
            description: "API is running"
        )
    ]
)]
class OpenApi
{
}