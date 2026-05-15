<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        // Route::post('/register', [AuthController::class, 'register']);
        // Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {

        Route::get('/me', [AuthController::class, 'me']);

        // Route::post('/switch-store', [AuthController::class, 'switchStore']);

        // Route::middleware(['store.selected'])->group(function () {

        //     // POS APIs
        //     Route::apiResource('products', ProductController::class);
        //     Route::apiResource('sales', SaleController::class);
        // });
    });
});
