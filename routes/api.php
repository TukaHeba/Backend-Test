<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\ProductController;
use App\Http\Controllers\v1\CategoryController;

Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    // Auth routes
    Route::controller(AuthController::class)->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
        Route::post('/logout', 'logout')->middleware('auth:sanctum');
    });

    // Category routes
    Route::controller(CategoryController::class)->group(function () {
        Route::get('/categories', 'index');
        Route::get('/categories/{category:slug}', 'show');
        Route::post('/categories', 'store')->middleware('auth:sanctum', 'role:admin');
        Route::put('/categories/{category:slug}', 'update')->middleware('auth:sanctum', 'role:admin');
        Route::delete('/categories/{category:slug}', 'destroy')->middleware('auth:sanctum', 'role:admin');
    });
});
