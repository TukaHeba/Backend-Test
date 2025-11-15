<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\AuthController;

Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    // Auth routes
    Route::controller(AuthController::class)->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
        Route::post('/logout', 'logout')->middleware('auth:sanctum');
    });
});
