<?php
use App\Http\Controllers\AddressController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CreditCardController;
use App\Http\Controllers\CheckoutController;
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
    Route::redirect('/cart', '/cart_item')
        ->name('cart.legacy');
    Route::view('/cart_item', 'shop.cart')
        ->name('cart');

    // Route Methods
    // get	Read data (e.g., Route::get('/users', ...))
    // post	Create data (Route::post('/users', ...))
    // put	Update data (Route::put('/users/{id}', ...))
    // patch	Partially update data
    // delete	Delete data

    Route::get('/checkout', [CheckoutController::class, 'show'])
        ->name('checkout');
    
    //addressController
    Route::view('/add-address', 'shop.address-form')
        ->name('address.create');
    Route::post('/add-address', [AddressController::class, 'store'])
        ->name('address.store');
    Route::get('/edit-address/{id}', [AddressController::class, 'edit'])
        ->whereNumber('id')
        ->name('address.edit');
    Route::patch('/edit-address/{id}', [AddressController::class, 'update'])
        ->whereNumber('id')
        ->name('address.update');
    Route::delete('/delivery-address/{id}', [AddressController::class, 'destroy'])
        ->whereNumber('id')
        ->name('address.destroy');

    //CreditCardController
    Route::get('/add-credit-card', [CreditCardController::class, 'create'])
        ->name('payment-method.create');
    Route::post('/add-credit-card', [CreditCardController::class, 'store'])
        ->name('payment-method.store');
    Route::delete('/credit-cards/{cardToken}', [CreditCardController::class, 'destroy'])
        ->where('cardToken', '[a-f0-9]{64}')
        ->name('payment-method.destroy');

    Route::view('/review', 'shop.review')->name('review.create');
    Route::redirect('/favorites', '/favorite')->name('favorites.legacy');
    Route::view('/favorite', 'shop.favorites')->name('favorites');
    Route::redirect('/orders', '/status-delivery')->name('orders.legacy');
    Route::view('/status-delivery', 'shop.orders')->name('orders');
});

require __DIR__.'/settings.php';
