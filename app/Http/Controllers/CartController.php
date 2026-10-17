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
        // Load the product relationship for displaying cart items.
        $cartItems = Auth::user()->cartItems()
            ->with('product')
            ->get();

        return view('shop.cart', compact('cartItems'));
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
            'tension' => 'nullable|integer',
        ]);

        $customerId = Auth::id();

        // 1. ตรวจสอบสต็อก (Stock) จากฐานข้อมูล ตามข้อตกลงในหน้า View
        $variant = productvariant::where('product_id', $validated['product_id'])
            ->where('color', $validated['color'])
            ->firstOrFail();

        // 2. ค้นหาสินค้าในตะกร้าโดยใช้ Composite Key ทั้ง 4 คอลัมน์
        $existingCartItem = cart_item::where([
            ['customer_id', '=', $customerId],
            ['product_id', '=', $validated['product_id']],
            ['color', '=', $validated['color']],
        ])->first();

        if ($existingCartItem) {
            // กรณีมีอยู่แล้ว: บวกจำนวนเพิ่ม
            $newQuantity = $existingCartItem->quantity + $validated['quantity'];

            if ($newQuantity > $variant->stock) {
                return back()->with('error', 'จำนวนสินค้าเกินกว่าสต็อกที่มีอยู่ (เหลือ ' . $variant->stock . ' ชิ้น)');
            }

            // สั่ง Update โดยใช้ Query Builder ตรงๆ เพื่อหลีกเลี่ยงบั๊ก Composite Key ของ Laravel
            cart_item::where([
                ['customer_id', '=', $customerId],
                ['product_id', '=', $validated['product_id']],
                ['color', '=', $validated['color']],
            ])->update([
                'quantity' => $newQuantity,
                // 'racket_tension' => $validated['tension'] // ถ้าตารางลบออกไปแล้ว ไม่ต้องใส่บรรทัดนี้
            ]);

        } else {
            // กรณียังไม่มี: ตรวจสอบสต็อกแล้ว Insert ใหม่
            if ($validated['quantity'] > $variant->stock) {
                return back()->with('error', 'exceeds the available stock (left: ' . $variant->stock . ' )');
            }

            cart_item::create([
                'customer_id' => $customerId,
                'product_id' => $validated['product_id'],
                'color' => $validated['color'],
                'quantity' => $validated['quantity'],
                // 'racket_tension' => $validated['tension'] // ถ้าตารางลบออกไปแล้ว ไม่ต้องใส่บรรทัดนี้
            ]);
        }

        return back()->with('status', 'added to cart successfully!');
    }

    /**
     * UPDATE: อัปเดตจำนวนสินค้าในหน้าตะกร้าโดยตรง
     */
    public function update(Request $request, int $productId, string $color)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = cart_item::query()->where([
            ['customer_id', '=', Auth::id()],
            ['product_id', '=', $productId],
            ['color', '=', $color],
        ])->firstOrFail();

        $variant = productvariant::query()
            ->where('product_id', $productId)
            ->where('color', $color)
            ->firstOrFail();

        if ($validated['quantity'] > $variant->stock) {
            return response()->json([
                'message' => 'Exceeds the available stock (left: '.$variant->stock.').',
            ], 422);
        }

        cart_item::query()->where([
            ['customer_id', '=', Auth::id()],
            ['product_id', '=', $productId],
            ['color', '=', $color],
        ])->update(['quantity' => $validated['quantity']]);

        return response()->json([
            'message' => 'Shopping cart updated successfully.',
            'quantity' => $validated['quantity'],
        ]);
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