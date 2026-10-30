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
        $cartItems = $request->user()
            ->cartItems()
            ->with('product')
            ->get();

        $variants = productvariant::query()
            ->whereIn('product_id', $cartItems->pluck('product_id'))
            ->get()
            ->keyBy(fn ($variant) => $variant->product_id . '_' . $variant->color);

        foreach ($cartItems as $item) {
            $item->variantRecord = $variants->get(
                $item->product_id . '_' . $item->color
            );
        }

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
                    'racket_tension' => $validated['tension'] ?? null,
                ]);
        } else {
            $user->cartItems()->create([
                'product_id' => $variant->product_id,
                'color' => $variant->color,
                'quantity' => $quantity,
                'racket_tension' => $validated['tension'] ?? null,
            ]);
        }

        return response()->json(['message' => 'Product added to cart.']);
    }

    public function update(Request $request, int $productId): JsonResponse
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
            $message = "Only {$variant->stock} item(s) are currently in stock.";

            return response()->json([
                'message' => $message,
                'errors' => ['quantity' => [$message]],
            ], 422);
        }

        cart_item::query()
            ->where('customer_id', $request->user()->id)
            ->where('product_id', $productId)
            ->where('color', $validated['color'])
            ->update(['quantity' => $validated['quantity']]);

        return response()->json([
            'message' => 'Cart updated.',
            'quantity' => $validated['quantity'],
        ]);
    }

    public function destroy(Request $request, int $productId): JsonResponse
    {
        $validated = $request->validate([
            'color' => ['required', 'string', 'max:50'],
        ]);

        $request->user()->cartItems()
            ->where('product_id', $productId)
            ->where('color', $validated['color'])
            ->firstOrFail()
            ->delete();

        return response()->json(['message' => 'Product removed from cart.']);
    }
}