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
    // Public Requirements
    Route::get('/requirements', [\App\Http\Controllers\Api\V1\RequirementController::class, 'index']);
    Route::get('/requirements/{id}', [\App\Http\Controllers\Api\V1\RequirementController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        // Protected Requirements
        Route::post('/requirements', [\App\Http\Controllers\Api\V1\RequirementController::class, 'store']);
        
        // Dashboard
        Route::prefix('dashboard')->group(function () {
            Route::get('/seller', [\App\Http\Controllers\Api\V1\DashboardController::class, 'sellerDashboard']);
        });

        // My Requirements
        Route::prefix('my/requirements')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\V1\RequirementController::class, 'myRequirements']);
            Route::get('{id}', [\App\Http\Controllers\Api\V1\RequirementController::class, 'myRequirementDetail']);
            Route::post('{id}', [\App\Http\Controllers\Api\V1\RequirementController::class, 'update']);
            Route::delete('{id}', [\App\Http\Controllers\Api\V1\RequirementController::class, 'destroy']);
        });

        // WebRTC Calling
        Route::prefix('call')->group(function () {
            Route::post('/initiate', [\App\Http\Controllers\Api\V1\WebRTCCallController::class, 'initiateCall']);
            Route::post('/answer', [\App\Http\Controllers\Api\V1\WebRTCCallController::class, 'answerCall']);
            Route::post('/signal', [\App\Http\Controllers\Api\V1\WebRTCCallController::class, 'relaySignal']);
            Route::post('/end', [\App\Http\Controllers\Api\V1\WebRTCCallController::class, 'endCall']);
            Route::post('/missed', [\App\Http\Controllers\Api\V1\WebRTCCallController::class, 'missedCall']);
            Route::get('/history', [\App\Http\Controllers\Api\V1\WebRTCCallController::class, 'callHistory']);
            Route::get('/active', [\App\Http\Controllers\Api\V1\WebRTCCallController::class, 'activeCall']);
        });

        // Chat & Conversations
        Route::prefix('chat')->group(function () {
            Route::get('/conversations', [\App\Http\Controllers\Api\V1\ChatController::class, 'getConversations']);
            Route::post('/conversations', [\App\Http\Controllers\Api\V1\ChatController::class, 'startConversation']);
            Route::get('/conversations/{id}/messages', [\App\Http\Controllers\Api\V1\ChatController::class, 'getMessages']);
            Route::post('/conversations/{id}/messages', [\App\Http\Controllers\Api\V1\ChatController::class, 'sendMessage']);
            Route::post('/conversations/{id}/read', [\App\Http\Controllers\Api\V1\ChatController::class, 'markAsRead']);
        });

        // Leads & Inquiries
        Route::prefix('leads')->group(function () {
            Route::post('/', [\App\Http\Controllers\Api\V1\LeadController::class, 'store']); // Capture Lead
        });

        Route::prefix('my/leads')->group(function () {
            Route::get('/received', [\App\Http\Controllers\Api\V1\LeadController::class, 'myReceivedLeads']);
            Route::get('/sent', [\App\Http\Controllers\Api\V1\LeadController::class, 'mySentLeads']);
            Route::put('/{id}/status', [\App\Http\Controllers\Api\V1\LeadController::class, 'updateStatus']);
        });
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