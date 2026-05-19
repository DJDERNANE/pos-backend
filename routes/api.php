<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/auth/switch-store', [AuthController::class, 'switchStore']);

        // Organizations
        Route::apiResource('organizations', \App\Http\Controllers\Api\V1\OrganizationController::class);

        // Stores
        Route::apiResource('stores', \App\Http\Controllers\Api\V1\StoreController::class);
        Route::post('stores/{store}/assign-user', [\App\Http\Controllers\Api\V1\StoreController::class, 'assignUser']);

        // Products
        Route::post('products/full', [\App\Http\Controllers\Api\V1\ProductController::class, 'storeFull']);
        Route::get('products/search', [\App\Http\Controllers\Api\V1\ProductController::class, 'search']);
        Route::apiResource('products', \App\Http\Controllers\Api\V1\ProductController::class);

        // Product Variants
        Route::get('products/{product}/variants', [\App\Http\Controllers\Api\V1\ProductVariantController::class, 'index']);
        Route::post('products/{product}/variants', [\App\Http\Controllers\Api\V1\ProductVariantController::class, 'store']);
        Route::post('products/{product}/variants/{variant}/set-default', [\App\Http\Controllers\Api\V1\ProductVariantController::class, 'setDefault']);
        Route::apiResource('variants', \App\Http\Controllers\Api\V1\ProductVariantController::class)->except(['index', 'store']);

        // Barcodes
        Route::get('variants/{variant}/barcodes', [\App\Http\Controllers\Api\V1\BarcodeController::class, 'index']);
        Route::post('variants/{variant}/barcodes', [\App\Http\Controllers\Api\V1\BarcodeController::class, 'store']);
        Route::delete('barcodes/{id}', [\App\Http\Controllers\Api\V1\BarcodeController::class, 'destroy']);

        // Pricing
        Route::get('pos/scan/{barcode}', [\App\Http\Controllers\Api\V1\PricingController::class, 'scan']);
        Route::apiResource('store-prices', \App\Http\Controllers\Api\V1\PricingController::class);

        // Cart / POS
        Route::get('cart', [\App\Http\Controllers\Api\V1\CartController::class, 'index']);
        Route::post('cart/scan', [\App\Http\Controllers\Api\V1\CartController::class, 'scan']);
        Route::post('cart/items/add', [\App\Http\Controllers\Api\V1\CartController::class, 'addItem']);
        Route::put('cart/items/{id}', [\App\Http\Controllers\Api\V1\CartController::class, 'updateItem']);
        Route::delete('cart/items/{id}', [\App\Http\Controllers\Api\V1\CartController::class, 'removeItem']);
        Route::delete('cart/clear', [\App\Http\Controllers\Api\V1\CartController::class, 'clear']);

        // Checkout
        Route::post('checkout', [\App\Http\Controllers\Api\V1\CheckoutController::class, 'store']);

        // Sales
        Route::get('sales', [\App\Http\Controllers\Api\V1\SalesController::class, 'index']);
        Route::get('sales/{id}', [\App\Http\Controllers\Api\V1\SalesController::class, 'show']);
        Route::get('sales/{id}/invoice', [\App\Http\Controllers\Api\V1\SalesController::class, 'invoice']);

        // Inventory ledger
        Route::get('inventory/movements', [\App\Http\Controllers\Api\V1\InventoryController::class, 'movements']);
        Route::get('inventory/stock/{variant}', [\App\Http\Controllers\Api\V1\InventoryController::class, 'stock']);
        Route::post('inventory/adjust', [\App\Http\Controllers\Api\V1\InventoryController::class, 'adjust']);

        // Inventory
        Route::post('inventory/bulk', [\App\Http\Controllers\Api\V1\InventoryController::class, 'bulkStore']);
        Route::get('inventory', [\App\Http\Controllers\Api\V1\InventoryController::class, 'index']);
        Route::get('inventory/low-stock', [\App\Http\Controllers\Api\V1\InventoryController::class, 'lowStock']);
        Route::get('inventory/search', [\App\Http\Controllers\Api\V1\InventoryController::class, 'search']);
        Route::get('inventory/{variant}', [\App\Http\Controllers\Api\V1\InventoryController::class, 'show']);
    });
});
