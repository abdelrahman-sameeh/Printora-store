<?php

use App\Http\Controllers\Web\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'index'])->name('home');
Route::get('/search', [StorefrontController::class, 'search'])->name('products.search');
Route::get('/products/{product}', [StorefrontController::class, 'show'])->name('products.show');
