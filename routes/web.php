<?php

use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:product_id}', [ProductController::class, 'show'])->name('products.show');

Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::post('/favorites/{product:product_id}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
});

require __DIR__.'/settings.php';