<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
// request หนึ่งรายการ เช่น GET /checkout จะเรียก route action ได้หนึ่งเมธอดเท่านั้น
// จึงไม่สามารถให้ route เดียวเรียก checkout() ในทั้ง AddressController และ CreditCardController พร้อมกันได้โดยตรง
class CheckoutController extends Controller
{
    public function show(): View
    {
        $user = Auth::user();
        $selectedItems = session('checkout.cart_items', []);

        $cartItems = collect();
        if ($selectedItems) {
            $cartItems = $user->cartItems()
                ->with('product')
                ->where(function ($query) use ($selectedItems) {
                    foreach ($selectedItems as $item) {
                        $query->orWhere(function ($query) use ($item) {
                            $query->where('product_id', $item['product_id'])
                                ->where('color', $item['color']);
                        });
                    }
                })
                ->get();
        }

        $delivery_addresses = $user
            ->Delivery_address()
            ->get(['delivery_id', 'name', 'phone', 'address'])
            ->map(fn ($address) => [
                'id' => $address->delivery_id,
                'name' => $address->name,
                'phone' => $address->phone,
                'address' => $address->address,
            ])
            ->values();

        $credit_debit_cards = $user
            ->cards()
            ->get(['card_no', 'expiry_date'])
            ->map(fn ($card) => [
                'last4' => substr($card->card_no, -4),
                'expiry_date' => $card->expiry_date->format('m/Y'),
                'delete_token' => hash_hmac(
                    'sha256',
                    $user->id.':'.$card->card_no,
                    (string) config('app.key')
                ),
            ])
            ->values();

        return view('shop.checkout', compact('delivery_addresses', 'credit_debit_cards', 'cartItems'));
    }

    public function selectItems(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.color' => ['required', 'string', 'max:50'],
        ]);

        $items = collect($validated['items'])
            ->unique(fn ($item) => $item['product_id'].'|'.$item['color'])
            ->values()
            ->all();

        if (count($items) !== count($validated['items'])) {
            return back()->withErrors(['items' => 'Duplicate cart items were submitted.']);
        }

        $matchingItems = Auth::user()->cartItems()
            ->where(function ($query) use ($items) {
                foreach ($items as $item) {
                    $query->orWhere(function ($query) use ($item) {
                        $query->where('product_id', $item['product_id'])
                            ->where('color', $item['color']);
                    });
                }
            })
            ->get(['product_id', 'color']);

        if ($matchingItems->count() !== count($items)) {
            return back()->withErrors(['items' => 'One or more selected items are no longer in your cart.']);
        }

        session(['checkout.cart_items' => $items]);

        return redirect()->route('checkout');
    }
}
