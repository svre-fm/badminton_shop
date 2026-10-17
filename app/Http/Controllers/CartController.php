<?php

namespace App\Http\Controllers;

use App\Models\cart_item;
use App\Models\productvariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cartItems = $request->user()->cartItems()->with('product')->get();

        return view('shop.cart', compact('cartItems'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,product_id'],
            'color' => ['required', 'string', 'max:50'],
            'quantity' => ['required', 'integer', 'min:1'],
            'tension' => ['nullable', 'integer', 'min:0', 'max:50'],
        ]);

        $variant = productvariant::query()
            ->where('product_id', $validated['product_id'])
            ->where('color', $validated['color'])
            ->firstOrFail();
        $user = $request->user();
        $cartItem = $user->cartItems()
            ->where('product_id', $variant->product_id)
            ->where('color', $variant->color)
            ->first();
        $quantity = ($cartItem?->quantity ?? 0) + $validated['quantity'];

        if ($quantity > $variant->stock) {
            $message = "Only {$variant->stock} item(s) are currently in stock.";

            return response()->json([
                'message' => $message,
                'errors' => ['quantity' => [$message]],
            ], 422);
        }

        if ($cartItem) {
            cart_item::query()
                ->where('customer_id', $user->id)
                ->where('product_id', $variant->product_id)
                ->where('color', $variant->color)
                ->update([
                    'quantity' => $quantity,
                    'racket_tension' => $validated['tension'] ?? 0,
                ]);
        } else {
            $user->cartItems()->create([
                'product_id' => $variant->product_id,
                'color' => $variant->color,
                'quantity' => $quantity,
                'racket_tension' => $validated['tension'] ?? 0,
            ]);
        }

        return response()->json(['message' => 'Product added to cart.']);
    }

    public function update(Request $request, int $productId)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'color' => ['required', 'string', 'max:50'],
        ]);

        $cartItem = $request->user()->cartItems()
            ->where('product_id', $productId)
            ->where('color', $validated['color'])
            ->firstOrFail();
        $variant = productvariant::query()
            ->where('product_id', $productId)
            ->where('color', $validated['color'])
            ->firstOrFail();

        if ($validated['quantity'] > $variant->stock) {
            return back()->withErrors([
                'quantity' => "Only {$variant->stock} item(s) are currently in stock.",
            ]);
        }

        cart_item::query()
            ->where('customer_id', $request->user()->id)
            ->where('product_id', $productId)
            ->where('color', $validated['color'])
            ->update(['quantity' => $validated['quantity']]);

        return back()->with('status', 'Cart updated.');
    }

    public function destroy(Request $request, int $productId)
    {
        $validated = $request->validate([
            'color' => ['required', 'string', 'max:50'],
        ]);

        $request->user()->cartItems()
            ->where('product_id', $productId)
            ->where('color', $validated['color'])
            ->firstOrFail()
            ->delete();

        return redirect()->route('cart')->with('status', 'Product removed from cart.');
    }
}
