<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/home')->name('root');

// HomeController@index ต้องส่งหมวดสินค้าและสินค้าขายดีจากฐานข้อมูลมาให้หน้า Home
Route::view('/home', 'home')->name('home');

Route::get('/shop', function () {
    $category = request()->query('category', 'badminton-racket');
    $query = request()->query();
    unset($query['category']);

    return redirect()->route('shop.index', ['category' => $category] + $query);
})->name('shop.legacy');

// category ใน URL เป็น slug สำหรับ UI; ProductController map ไป product.product_type (RACKET/STRING/GRIP/SHUTTLECOCK)
// ส่งคอลัมน์ product และข้อมูล product_variant จาก EER พร้อม filter/sort ที่ query จากฐานข้อมูล
Route::view('/category/{category?}', 'shop.index')
    ->whereIn('category', ['badminton-racket', 'badminton-string', 'grip', 'shuttlecock'])
    ->name('shop.index');

Route::redirect('/product/{product_id}', '/product/badminton-racket/{product_id}')
    ->whereNumber('product_id')
    ->name('shop.product.legacy');

// product_id คือ PK ตาม EER; ดึง product, subtype, product_variant, racket_tension, review.star/date/comment
// max_tension เป็นค่าคำนวณจาก racket_tension; ให้ตรวจ tension ขั้นต่ำ 18 และบันทึกลง cart_item/order_item ตามข้อตกลง
Route::view('/product/{category}/{product_id}', 'shop.product')
    ->whereIn('category', ['badminton-racket', 'badminton-string', 'grip', 'shuttlecock'])
    ->whereNumber('product_id')
    ->name('shop.product');

Route::redirect('/dashboard', '/home');

Route::middleware('auth')->group(function () {
    Route::redirect('/cart', '/cart_item')->name('cart.legacy');

    // CartController query cart_item โดย customer_id พร้อม product/product_variant; quantity คือชื่อคอลัมน์ตาม EER
    // tension ยังไม่มีใน diagram ล่าสุด แต่ต้องเพิ่มลง cart_item ตามข้อตกลงล่าสุดก่อนจัดเก็บ tension จริง
    Route::view('/cart_item', 'shop.cart')->name('cart');

    // CheckoutController ใช้ delivery_address.address, cart_item, orders.shipping, order_item.unit_price/quantity และ payment
    // Schema orders ปัจจุบันไม่มี delivery_address FK/snapshot; ตกลงวิธีรักษาที่อยู่ของคำสั่งซื้อก่อนสร้าง order
    Route::view('/checkout', 'shop.checkout')->name('checkout');

    // DeliveryAddressController ต้องเพิ่ม route POST/PATCH/DELETE สำหรับบันทึก แก้ และลบที่อยู่
    Route::view('/add-address', 'shop.address-form')->name('address.create');
    Route::view('/edit-address/{id}', 'shop.address-form')->name('address.edit');

    // credit_debit_card ใช้ composite key (customer_id, number); payment.card_id แบบ int ยังไม่ตรงกับ number แบบ varchar
    // ให้ตกลง key/reference และใช้ token จาก payment provider แทนเก็บเลขบัตรเต็ม/CVV
    Route::view('/add-credit-card', 'shop.add-credit-card')->name('payment-method.create');

    // review ใช้ star/date/comment และผูก customer_id, product_id, order_no; ตรวจ order_item ว่าซื้อสินค้านี้จริง
    Route::view('/review', 'shop.review')->name('review.create');

    Route::redirect('/favorites', '/favorite')->name('favorites.legacy');

    // favorite ใช้ composite key (customer_id, product_id); map customer_id เข้ากับ users.id ให้ตกลงก่อน
    Route::view('/favorite', 'shop.favorites')->name('favorites');

    Route::redirect('/orders', '/status-delivery')->name('orders.legacy');

    // OrderController ส่ง orders.order_no/order_date/status/shipping และ relation order_item ตาม EER
    Route::view('/status-delivery', 'shop.orders')->name('orders');
});

require __DIR__.'/settings.php';
