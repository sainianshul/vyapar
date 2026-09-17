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
        Route::post('send-otp', [\App\Http\Controllers\Api\V1\AuthController::class, 'sendOtp']);
        Route::post('verify-otp', [\App\Http\Controllers\Api\V1\AuthController::class, 'verifyOtp']);
    });





    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('logout', [\App\Http\Controllers\Api\V1\AuthController::class, 'logout']);
            Route::post('logout-all', [\App\Http\Controllers\Api\V1\AuthController::class, 'logoutAll']);
            Route::get('me', [\App\Http\Controllers\Api\V1\AuthController::class, 'me']);
        });

        Route::post('profile', [\App\Http\Controllers\Api\V1\ProfileController::class, 'update']);

        // Categories
        Route::prefix('categories')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\V1\CategoryController::class, 'index']);
            Route::get('featured', [\App\Http\Controllers\Api\V1\CategoryController::class, 'featured']);
            Route::get('{id}', [\App\Http\Controllers\Api\V1\CategoryController::class, 'show']);
        });

        // Products
        Route::prefix('products')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\V1\ProductController::class, 'index']);
            Route::get('featured', [\App\Http\Controllers\Api\V1\ProductController::class, 'featured']);
            Route::get('{id}', [\App\Http\Controllers\Api\V1\ProductController::class, 'show']);
            Route::post('/', [\App\Http\Controllers\Api\V1\ProductController::class, 'store']);
            Route::post('{id}', [\App\Http\Controllers\Api\V1\ProductController::class, 'update']);
        });
        
        // My Products
        Route::prefix('my/products')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\V1\ProductController::class, 'myProducts']);
            Route::get('{id}', [\App\Http\Controllers\Api\V1\ProductController::class, 'myProductDetail']);
            Route::post('{id}/status', [\App\Http\Controllers\Api\V1\ProductController::class, 'updateStatus']);
            Route::delete('{id}', [\App\Http\Controllers\Api\V1\ProductController::class, 'destroy']);
        });
    });
});