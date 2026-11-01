<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CreditCardController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\StatusController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/home')->name('root');

Route::get('/home', [ProductController::class, 'home'])->name('home');

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
    Route::get('/cart_item', [CartController::class, 'index'])->name('cart');
    Route::post('/cart_item', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart_item/{productId}', [CartController::class, 'update'])
        ->whereNumber('productId')
        ->name('cart.update');
    Route::delete('/cart_item/{productId}', [CartController::class, 'destroy'])
        ->whereNumber('productId')
        ->name('cart.destroy');

    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
    Route::post('/checkout/items', [CheckoutController::class, 'selectItems'])->name('checkout.items');
    Route::post('/checkout/buy-now', [CheckoutController::class, 'buyNow'])->name('checkout.buy-now');
    Route::post('/checkout', [CheckoutController::class, 'placeOrder'])->name('checkout.place');

    Route::get('/add-address', [AddressController::class, 'create'])->name('address.create');
    Route::post('/add-address', [AddressController::class, 'store'])->name('address.store');
    Route::get('/edit-address/{address}', [AddressController::class, 'edit'])->whereNumber('address')->name('address.edit');
    Route::put('/edit-address/{address}', [AddressController::class, 'update'])->whereNumber('address')->name('address.update');
    Route::delete('/address/{address}', [AddressController::class, 'destroy'])->whereNumber('address')->name('address.destroy');

    Route::get('/add-credit-card', [CreditCardController::class, 'create'])->name('payment-method.create');
    Route::post('/add-credit-card', [CreditCardController::class, 'store'])->name('payment-method.store');

    Route::get('/review', [ReviewController::class, 'create'])->name('review.create');
    Route::post('/product/{product}/review', [ReviewController::class, 'store'])->whereNumber('product')->name('review.store');
    Route::get('/product/{product}/review/{review}/edit', [ReviewController::class, 'edit'])->whereNumber('product')->whereNumber('review')->name('review.edit');
    Route::put('/product/{product}/review/{review}', [ReviewController::class, 'update'])->whereNumber('product')->whereNumber('review')->name('review.update');
    Route::delete('/product/{product}/review/{review}', [ReviewController::class, 'destroy'])->whereNumber('product')->whereNumber('review')->name('review.destroy');

    Route::redirect('/favorites', '/favorite')->name('favorites.legacy');
    Route::get('/favorite', [FavoriteController::class, 'index'])->name('favorites');
    Route::post('/favorite/{product}/toggle', [FavoriteController::class, 'toggle'])
        ->whereNumber('product')
        ->name('favorites.toggle');

    Route::redirect('/orders', '/status-delivery')->name('orders.legacy');
    Route::get('/status-delivery', [StatusController::class, 'index'])->name('orders');
});

require __DIR__.'/settings.php';
