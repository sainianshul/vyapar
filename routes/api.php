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

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('logout', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'logout']);
            Route::post('logout-all', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'logoutAll']);
            Route::get('me', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'me']);
        });
    });
});