<?php

use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

// Customer
Route::middleware(['auth:sanctum', 'roles:user'])->group(function () {
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::post('orders', [OrderController::class, 'store']);
});

// Seller
Route::middleware(['auth:sanctum', 'roles:seller'])->group(function () {
    Route::get('seller/orders', [OrderController::class, 'sellerOrders']);
    Route::put('seller/sub-orders/{subOrder}/status', [OrderController::class, 'updateSubOrderStatus']);
});
