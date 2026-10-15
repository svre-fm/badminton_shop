<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/home')->name('root');

Route::view('/home', 'home')->name('home');

Route::get('/shop', function () {
    $category = request()->query('category', 'badminton-racket');
    $query = request()->query();
    unset($query['category']);

    return redirect()->route('shop.index', ['category' => $category] + $query);
})->name('shop.legacy');

// เปลี่ยนจาก Route::view → Controller (path + name เดิมทุกตัว)
Route::get('/category/{category?}', [ProductController::class, 'index'])
    ->whereIn('category', ['badminton-racket', 'badminton-string', 'grip', 'shuttlecock'])
    ->name('shop.index');

Route::redirect('/product/{product_id}', '/product/badminton-racket/{product_id}')
    ->whereNumber('product_id')
    ->name('shop.product.legacy');

Route::get('/product/{category}/{product_id}', [ProductController::class, 'show'])
    ->whereIn('category', ['badminton-racket', 'badminton-string', 'grip', 'shuttlecock'])
    ->whereNumber('product_id')
    ->name('shop.product');

Route::redirect('/dashboard', '/home');

Route::middleware('auth')->group(function () {
    Route::redirect('/cart', '/cart_item')->name('cart.legacy');
    Route::view('/cart_item', 'shop.cart')->name('cart');

    Route::view('/checkout', 'shop.checkout')->name('checkout');

    Route::view('/add-address', 'shop.address-form')->name('address.create');
    Route::view('/edit-address/{id}', 'shop.address-form')->name('address.edit');

    Route::view('/add-credit-card', 'shop.add-credit-card')->name('payment-method.create');

    Route::view('/review', 'shop.review')->name('review.create');

    Route::redirect('/favorites', '/favorite')->name('favorites.legacy');
    Route::view('/favorite', 'shop.favorites')->name('favorites');

    Route::redirect('/orders', '/status-delivery')->name('orders.legacy');
    Route::view('/status-delivery', 'shop.orders')->name('orders');
});

require __DIR__.'/settings.php';
