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

    // Product routes
    Route::controller(ProductController::class)->group(function () {
        Route::get('/products', 'index');
        Route::post('/products', 'store')->middleware('auth:sanctum' , 'role:admin');

        Route::get('/products/trashed', 'trashed')->middleware('auth:sanctum' , 'role:admin'    );
        Route::post('/products/{slug}/restore', 'restore')->middleware('auth:sanctum' , 'role:admin');
        Route::delete('/products/{slug}/force', 'forceDelete')->middleware('auth:sanctum' , 'role:admin');

        Route::get('/products/{product:slug}', 'show');
        Route::put('/products/{product:slug}', 'update')->middleware('auth:sanctum' , 'role:admin');
        Route::delete('/products/{product:slug}', 'destroy')->middleware('auth:sanctum' , 'role:admin');
    });

    // Category routes
    Route::controller(CategoryController::class)->group(function () {
        Route::get('/categories', 'index');
        Route::get('/categories/{category:slug}', 'show');
        Route::post('/categories', 'store')->middleware('auth:sanctum', 'role:admin');
        Route::put('/categories/{category:slug}', 'update')->middleware('auth:sanctum', 'role:admin');
        Route::delete('/categories/{category:slug}', 'destroy')->middleware('auth:sanctum', 'role:admin');
    });

    Route::controller(CartController::class)->middleware('auth:sanctum')->group(function () {
        Route::get('/cart', 'index');
        Route::post('/cart', 'store');
        Route::delete('/cart', 'clear');
        Route::delete('/cart/{cartItem}', 'destroy');
    });
});
