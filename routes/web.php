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
        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('dashboard');

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
        });
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);

        // Comments (Admin Notes)
        Route::post('comments', [\App\Http\Controllers\Admin\CommentController::class, 'store'])->name('comments.store');
        Route::delete('comments/{comment}', [\App\Http\Controllers\Admin\CommentController::class, 'destroy'])->name('comments.destroy');
        // Categories CRUD
        Route::get('categories/data', [\App\Http\Controllers\Admin\CategoryController::class, 'data'])->name('categories.data');
        Route::post('categories/{category}/status', [\App\Http\Controllers\Admin\CategoryController::class, 'updateStatus'])->name('categories.update-status');
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class)->except(['show']);

        // Add future routes here for Nurses, Patients, Error Logs, etc.
    });
    });
});