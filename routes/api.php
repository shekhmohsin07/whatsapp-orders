<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PublicStoreController;
use App\Http\Controllers\Api\StoreController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API (no authentication)
|--------------------------------------------------------------------------
*/
Route::prefix('public')->group(function () {
    Route::get('stores/{slug}', [PublicStoreController::class, 'show']);
    Route::get('stores/{slug}/products', [PublicStoreController::class, 'products']);
    Route::post('stores/{slug}/orders', [PublicStoreController::class, 'storeOrder'])
        ->middleware('throttle:20,1'); // basic spam protection
});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
Route::post('register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');

/*
|--------------------------------------------------------------------------
| Business API (Sanctum token required)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);

    // Stores: index, store, show, update, destroy
    Route::apiResource('stores', StoreController::class);

    // Products
    Route::get('stores/{store}/products', [ProductController::class, 'index']);
    Route::post('stores/{store}/products', [ProductController::class, 'store']);
    Route::get('products/{product}', [ProductController::class, 'show']);
    Route::put('products/{product}', [ProductController::class, 'update']);
    Route::delete('products/{product}', [ProductController::class, 'destroy']);

    // Orders
    Route::get('stores/{store}/orders', [OrderController::class, 'index']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::put('orders/{order}/status', [OrderController::class, 'updateStatus']);
});