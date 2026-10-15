<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
// request หนึ่งรายการ เช่น GET /checkout จะเรียก route action ได้หนึ่งเมธอดเท่านั้น
// จึงไม่สามารถให้ route เดียวเรียก checkout() ในทั้ง AddressController และ CreditCardController พร้อมกันได้โดยตรง
class CheckoutController extends Controller
{
    public function show(): View
    {
        $user = Auth::user();

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

        return view('shop.checkout', compact('delivery_addresses', 'credit_debit_cards'));
    }
}
