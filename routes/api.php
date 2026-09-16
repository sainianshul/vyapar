<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API is running',
    ]);
});

Route::prefix('v1')->group(function () {
    // Public Routes
    Route::prefix('auth')->group(function () {
        Route::post('send-otp', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'sendOtp']);
        Route::post('verify-otp', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'verifyOtp']);
    });

    // Categories (Public - no auth needed)
    Route::prefix('categories')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\CategoryController::class, 'index']);
        Route::get('featured', [\App\Http\Controllers\Api\V1\CategoryController::class, 'featured']);
        Route::get('{slug}', [\App\Http\Controllers\Api\V1\CategoryController::class, 'show']);
    });

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('logout', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'logout']);
            Route::post('logout-all', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'logoutAll']);
            Route::get('me', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'me']);
        });
    });
});