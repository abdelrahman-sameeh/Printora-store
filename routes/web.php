<?php

use App\Http\Controllers\AuthWebController;
use App\Http\Controllers\CategoryWebController;
use App\Http\Controllers\ProductWebController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [StorefrontController::class, 'index'])->name('home');
Route::get('/search', [StorefrontController::class, 'search'])->name('products.search');
Route::get('/products/{product}', [StorefrontController::class, 'show'])->name('products.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthWebController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthWebController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthWebController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/home', [AuthWebController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');

    Route::middleware('roles:seller')->prefix('seller')->name('seller.')->group(function () {
        Route::get('/products', [ProductWebController::class, 'index'])->name('products.index');
        Route::get('/products/create', [ProductWebController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductWebController::class, 'store'])->name('products.store');
        Route::get('/products/{product}', [ProductWebController::class, 'show'])->name('products.show');
        Route::get('/products/{product}/edit', [ProductWebController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductWebController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductWebController::class, 'destroy'])->name('products.destroy');
    });

    Route::middleware('roles:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/categories', [CategoryWebController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryWebController::class, 'store'])->name('categories.store');
        Route::delete('/categories/{category}', [CategoryWebController::class, 'destroy'])->name('categories.destroy');
        Route::post('/sub-categories', [CategoryWebController::class, 'storeSubCategory'])->name('sub-categories.store');
        Route::delete('/sub-categories/{subCategory}', [CategoryWebController::class, 'destroySubCategory'])->name('sub-categories.destroy');
    });
});
