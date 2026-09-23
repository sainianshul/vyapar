<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::prefix('admin')->group(function () {
    // Auth Routes (Named login instead of admin.login)
    Route::get('login', [\App\Http\Controllers\Admin\AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->name('login.post');
    Route::post('logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout');

    Route::name('admin.')->group(function () {

    Route::middleware(['auth'])->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/stats', [\App\Http\Controllers\Admin\DashboardController::class, 'stats'])->name('dashboard.stats');

        // Users (Buyers/Sellers) CRUD
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('data', [\App\Http\Controllers\Admin\UserController::class, 'data'])->name('data');
            
            Route::get('blocked', [\App\Http\Controllers\Admin\UserController::class, 'blocked'])->name('blocked');
            Route::get('blocked/data', [\App\Http\Controllers\Admin\UserController::class, 'blockedData'])->name('blocked.data');
            Route::post('{user}/unblock', [\App\Http\Controllers\Admin\UserController::class, 'unblock'])->name('unblock');
            
            Route::get('deleted', [\App\Http\Controllers\Admin\UserController::class, 'deleted'])->name('deleted');
            Route::get('deleted/data', [\App\Http\Controllers\Admin\UserController::class, 'deletedData'])->name('deleted.data');
            Route::post('{user}/restore', [\App\Http\Controllers\Admin\UserController::class, 'restore'])->name('restore');
            
            Route::post('{user}/status', [\App\Http\Controllers\Admin\UserController::class, 'updateStatus'])->name('update-status');
            Route::post('{user}/revoke-token', [\App\Http\Controllers\Admin\UserController::class, 'revokeToken'])->name('revoke-token');
            Route::get('{user}/products/data', [\App\Http\Controllers\Admin\UserController::class, 'userProductsData'])->name('products.data');
            Route::get('{user}/requirements/data', [\App\Http\Controllers\Admin\UserController::class, 'userRequirementsData'])->name('requirements.data');
            Route::get('{user}/leads/data', [\App\Http\Controllers\Admin\UserController::class, 'userLeadsData'])->name('leads.data');
        });
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);

        // Comments (Admin Notes)
        Route::post('comments', [\App\Http\Controllers\Admin\CommentController::class, 'store'])->name('comments.store');
        Route::delete('comments/{comment}', [\App\Http\Controllers\Admin\CommentController::class, 'destroy'])->name('comments.destroy');
        // Categories CRUD
        Route::get('categories/data', [\App\Http\Controllers\Admin\CategoryController::class, 'data'])->name('categories.data');
        Route::get('categories/{category}/children', [\App\Http\Controllers\Admin\CategoryController::class, 'children'])->name('categories.children');
        Route::post('categories/{category}/status', [\App\Http\Controllers\Admin\CategoryController::class, 'updateStatus'])->name('categories.update-status');
        Route::post('categories/{category}/featured', [\App\Http\Controllers\Admin\CategoryController::class, 'updateFeatured'])->name('categories.update-featured');
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);

        // Products CRUD
        Route::get('products/data', [\App\Http\Controllers\Admin\ProductController::class, 'data'])->name('products.data');
        Route::post('products/{product}/status', [\App\Http\Controllers\Admin\ProductController::class, 'updateStatus'])->name('products.update-status');
        Route::post('products/{product}/featured', [\App\Http\Controllers\Admin\ProductController::class, 'updateFeatured'])->name('products.update-featured');
        Route::get('products/{product}/views-data', [\App\Http\Controllers\Admin\ProductController::class, 'viewsData'])->name('products.views-data');
        Route::get('products/{product}/leads-data', [\App\Http\Controllers\Admin\ProductController::class, 'leadsData'])->name('products.leads-data');
        Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);

        // Leads
        Route::get('leads/{lead}', [\App\Http\Controllers\Admin\LeadController::class, 'show'])->name('leads.show');

        // Banners CRUD
        Route::get('banners/data', [\App\Http\Controllers\Admin\BannerController::class, 'data'])->name('banners.data');
        Route::post('banners/{banner}/status', [\App\Http\Controllers\Admin\BannerController::class, 'updateStatus'])->name('banners.update-status');
        Route::resource('banners', \App\Http\Controllers\Admin\BannerController::class)->except(['create', 'edit', 'show']);

        // Communication Logs
        Route::post('communication-logs/truncate', [\App\Http\Controllers\Admin\CommunicationLogController::class, 'truncate'])->name('communication-logs.truncate');
        Route::resource('communication-logs', \App\Http\Controllers\Admin\CommunicationLogController::class)->only(['index', 'destroy']);

        Route::get('feedbacks/data', [\App\Http\Controllers\Admin\FeedbackController::class, 'data'])->name('feedbacks.data');
        Route::post('feedbacks/{feedback}/status', [\App\Http\Controllers\Admin\FeedbackController::class, 'updateStatus'])->name('feedbacks.update-status');
        Route::resource('feedbacks', \App\Http\Controllers\Admin\FeedbackController::class)->except(['create', 'edit', 'show']);

        // Requirements CRUD
        Route::get('requirements/data', [\App\Http\Controllers\Admin\RequirementController::class, 'data'])->name('requirements.data');
        Route::post('requirements/{requirement}/status', [\App\Http\Controllers\Admin\RequirementController::class, 'updateStatus'])->name('requirements.update-status');
        Route::resource('requirements', \App\Http\Controllers\Admin\RequirementController::class);

    });
    });
});