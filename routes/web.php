<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:product_id}', [ProductController::class, 'show'])->name('products.show');

require __DIR__.'/settings.php';
