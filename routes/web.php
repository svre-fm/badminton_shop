<?php

use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StatusController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'home'])->name('home');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:product_id}', [ProductController::class, 'show'])->name('products.show');

Route::middleware(['auth'])->group(function () {
    Route::get('/', [ProductController::class, 'home'])->name('home');

    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/{product:product_id}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::get('/orders/status', [StatusController::class, 'index'])->name('orders.status');
});

require __DIR__.'/settings.php';