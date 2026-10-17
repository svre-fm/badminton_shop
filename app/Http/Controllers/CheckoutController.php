<?php

namespace App\Http\Controllers;

use App\Models\orders;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $selectedItems = session('checkout.cart_items');
        $cartItemsQuery = $user->cartItems()->with('product');

        if (is_array($selectedItems)) {
            $cartItemsQuery->where(function ($query) use ($selectedItems) {
                foreach ($selectedItems as $item) {
                    $query->orWhere(function ($query) use ($item) {
                        $query->where('product_id', $item['product_id'])
                            ->where('color', $item['color']);
                    });
                }
            });
        }

        $cartItems = $cartItemsQuery->get();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Add products to your cart before checkout.');
        }

        $deliveryAddresses = $user->Delivery_address()->get();
        $creditCards = $user->cards()
            ->get()
            ->filter(fn ($card) => Carbon::parse($card->expiry_date)->endOfMonth()->endOfDay()->isFuture())
            ->values();
        $cardTokens = $creditCards->mapWithKeys(fn ($card) => [
            $card->card_no => hash_hmac(
                'sha256',
                $user->id.':'.$card->card_no,
                (string) config('app.key')
            ),
        ]);

        return view('shop.checkout', compact('cartItems', 'deliveryAddresses', 'creditCards', 'cardTokens'));
    }

    public function selectItems(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.color' => ['required', 'string', 'max:50'],
            'items.*.selected' => ['sometimes', 'boolean'],
        ]);

        $items = collect($validated['items'])
            ->filter(fn ($item) => ! array_key_exists('selected', $item) || filter_var($item['selected'], FILTER_VALIDATE_BOOLEAN))
            ->map(fn ($item) => [
                'product_id' => $item['product_id'],
                'color' => $item['color'],
            ])
            ->values();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['items' => 'Select at least one product to continue to checkout.']);
        }

        $uniqueItems = $items
            ->unique(fn ($item) => $item['product_id'].'|'.$item['color'])
            ->values();

        if ($uniqueItems->count() !== $items->count()) {
            throw ValidationException::withMessages(['items' => 'Duplicate cart items were submitted.']);
        }

        $matchingCount = $request->user()->cartItems()
            ->where(function ($query) use ($items) {
                foreach ($items as $item) {
                    $query->orWhere(function ($query) use ($item) {
                        $query->where('product_id', $item['product_id'])
                            ->where('color', $item['color']);
                    });
                }
            })
            ->count();

        if ($matchingCount !== $items->count()) {
            throw ValidationException::withMessages(['items' => 'One or more selected items are no longer in your cart.']);
        }

        $request->session()->put('checkout.cart_items', $items->all());

        return redirect()->route('checkout');
    }

    public function placeOrder(Request $request)
    {
        $validated = $request->validate([
            'delivery_id' => ['required', 'integer'],
            'card_token' => ['required', 'string', 'size:64'],
        ]);

        $user = $request->user();
        $address = $user->Delivery_address()
            ->where('delivery_id', $validated['delivery_id'])
            ->firstOrFail();
        $card = $user->cards()->get()->first(function ($card) use ($user, $validated) {
            $token = hash_hmac(
                'sha256',
                $user->id.':'.$card->card_no,
                (string) config('app.key')
            );

            return hash_equals($token, $validated['card_token']);
        });
        abort_unless($card, 404);

        if (! $card->expiry_date || Carbon::parse($card->expiry_date)->endOfMonth()->endOfDay()->isPast()) {
            throw ValidationException::withMessages(['card_token' => 'This card has expired.']);
        }

        $selectedItems = $request->session()->get('checkout.cart_items');

        $order = DB::transaction(function () use ($user, $address, $card, $selectedItems) {
            $cartQuery = $user->cartItems();
            if (is_array($selectedItems)) {
                $cartQuery->where(function ($query) use ($selectedItems) {
                    foreach ($selectedItems as $item) {
                        $query->orWhere(function ($query) use ($item) {
                            $query->where('product_id', $item['product_id'])
                                ->where('color', $item['color']);
                        });
                    }
                });
            }

            $cartItems = $cartQuery->lockForUpdate()->get();
            if ($cartItems->isEmpty() || (is_array($selectedItems) && $cartItems->count() !== count($selectedItems))) {
                throw ValidationException::withMessages(['items' => 'Your selected cart items have changed. Please review your cart.']);
            }

            $orderItems = [];
            $total = 0.0;
            foreach ($cartItems as $cartItem) {
                $variant = DB::table('productvariants')
                    ->where('product_id', $cartItem->product_id)
                    ->where('color', $cartItem->color)
                    ->lockForUpdate()
                    ->first();

                if (! $variant || $variant->stock < $cartItem->quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Insufficient stock for {$cartItem->product?->name}. Please update your cart.",
                    ]);
                }

                $unitPrice = (float) $cartItem->product->price;
                $total += $unitPrice * $cartItem->quantity;
                $orderItems[] = [
                    'product_id' => $cartItem->product_id,
                    'color' => $cartItem->color,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $unitPrice,
                    'tension' => $cartItem->racket_tension,
                ];
            }

            $orderNo = 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(10));
            $order = orders::query()->create([
                'order_no' => $orderNo,
                'customer_id' => $user->id,
                'status' => 'To ship',
                'order_date' => now()->toDateString(),
                'delivery_id' => $address->delivery_id,
                'total_price' => round($total, 2),
            ]);

            foreach ($cartItems as $index => $cartItem) {
                DB::table('order_items')->insert([
                    'order_no' => $orderNo,
                    ...$orderItems[$index],
                ]);
                DB::table('productvariants')
                    ->where('product_id', $cartItem->product_id)
                    ->where('color', $cartItem->color)
                    ->decrement('stock', $cartItem->quantity);
            }

            DB::table('payments')->insert([
                'user_id' => $user->id,
                'order_no' => $orderNo,
                'card_no' => $card->card_no,
                'amount' => round($total, 2),
                'method' => 'CREDIT_CARD',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $cartItems->each->delete();

            return $order;
        });

        $request->session()->forget('checkout.cart_items');

        return redirect()->route('orders.status', ['status' => 'to_ship'])
            ->with('status', "Order {$order->order_no} placed successfully.");
    }
}
