<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\cart_item;
use App\Models\productvariant;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * READ: แสดงหน้ารายการสินค้าในตะกร้า
     */
    public function index()
    {
        // ดึงข้อมูลตะกร้าของลูกค้าปัจจุบัน พร้อม Eager Load สินค้าหลักและรุ่นย่อย (สี) เพื่อนำไปแสดงผล
        $cartItems = Auth::user()->cartItems()
            ->with(['product', 'variantRecord'])
            ->get();

        return view('cart.index', compact('cartItems'));
    }

    /**
     * CREATE / UPDATE: บันทึกสินค้าลงตะกร้า (จากหน้า product.blade.php)
     */
    public function store(Request $request)
    {
        // ตรวจสอบข้อมูลที่ส่งมาจาก Frontend (Alpine.js ส่ง tension มาเป็น 0 หากไม่ใช่ไม้แบดมินตัน)
        $validated = $request->validate([
            'product_id' => 'required|integer',
            'color' => 'required|string|max:50',
            'quantity' => 'required|integer|min:1',
            'tension' => 'required|integer', 
        ]);

        $customerId = Auth::id();

        // 1. ตรวจสอบสต็อก (Stock) จากฐานข้อมูล ตามข้อตกลงในหน้า View
        $variant = productvariant::where('product_id', $validated['product_id'])
            ->where('color', $validated['color'])
            ->firstOrFail();

        // 2. ค้นหาสินค้าในตะกร้าโดยใช้ Composite Key ทั้ง 4 คอลัมน์
        $cartItem = cart_item::firstOrNew([
            'customer_id' => $customerId,
            'product_id' => $validated['product_id'],
            'color' => $validated['color'],
            'racket_tension' => $validated['tension'],
        ]);

        // คำนวณจำนวนสินค้าใหม่ (ถ้ามีในตะกร้าอยู่แล้วให้บวกเพิ่ม)
        $newQuantity = $cartItem->exists ? $cartItem->quantity + $validated['quantity'] : $validated['quantity'];

        // ตรวจสอบว่าจำนวนที่ต้องการสั่งซื้อเกินสต็อกที่มีหรือไม่
        if ($newQuantity > $variant->stock) {
            return back()->with('error', 'Exceeds the available stock (left: ' . $variant->stock );
        }

        // บันทึกข้อมูล
        $cartItem->quantity = $newQuantity;
        $cartItem->save();

        return back()->with('status', 'Item added to cart successfully!');
    }

    /**
     * UPDATE: อัปเดตจำนวนสินค้าในหน้าตะกร้าโดยตรง
     */
    public function update(Request $request, $productId, $color, $tension)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = cart_item::where([
            ['customer_id', '=', Auth::id()],
            ['product_id', '=', $productId],
            ['color', '=', $color],
            ['racket_tension', '=', $tension],
        ])->firstOrFail();

        // ตรวจสอบสต็อกอีกครั้งก่อนอัปเดต
        $variant = $cartItem->variantRecord();
        if ($validated['quantity'] > $variant->stock) {
            return back()->with('error', 'Exceeds the available stock!');
        }

        $cartItem->update(['quantity' => $validated['quantity']]);

        return back()->with('status', 'Shopping cart updated successfully');
    }

    /**
     * DELETE: ลบสินค้าออกจากตะกร้า
     */
    public function destroy($productId, $color, $tension)
    {
        $cartItem = cart_item::where([
            ['customer_id', '=', Auth::id()],
            ['product_id', '=', $productId],
            ['color', '=', $color],
            ['racket_tension', '=', $tension],
        ])->firstOrFail();

        $cartItem->delete();

        return back()->with('status', 'Product removed from the cart.');
    }
}