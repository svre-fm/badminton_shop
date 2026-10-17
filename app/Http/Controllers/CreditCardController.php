<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class CreditCardController extends Controller
{
    public function index(Request $request)
    {
        $creditCards = $request->user()->cards()->get();
        $cardTokens = $creditCards->mapWithKeys(fn ($card) => [
            $card->card_no => $this->tokenFor($request, $card->card_no),
        ]);

        return view('credit-cards.index', compact('creditCards', 'cardTokens'));
    }

    public function create()
    {
        return view('credit-cards.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'card_no' => [
                'required',
                'string',
                'regex:/^[0-9]{14,19}$/',
                function (string $attribute, string $value, \Closure $fail) {
                    if (! $this->passesLuhn($value)) {
                        $fail('Enter a valid card number.');
                    }
                },
            ],
            'expiry_date' => ['required', 'date_format:Y-m'],
        ]);

        if (Carbon::createFromFormat('!Y-m', $validated['expiry_date'])->endOfMonth()->endOfDay()->isPast()) {
            return back()->withErrors(['expiry_date' => 'The card expiry date must be in the future.'])->withInput();
        }

        if ($request->user()->cards()->where('card_no', $validated['card_no'])->exists()) {
            return back()->withErrors(['card_no' => 'This card is already saved.'])->withInput();
        }

        $request->user()->cards()->create([
            'card_no' => $validated['card_no'],
            'expiry_date' => $validated['expiry_date'].'-01',
        ]);

        return redirect()->route('checkout')->with('status', 'Card saved. You can select it at checkout.');
    }

    public function destroy(Request $request, string $token)
    {
        $card = $request->user()->cards()->get()->first(
            fn ($card) => hash_equals($this->tokenFor($request, $card->card_no), $token)
        );

        abort_unless($card, 404);
        $card->delete();

        return redirect()->route('credit-cards.index')->with('status', 'Card removed.');
    }

    private function tokenFor(Request $request, string $cardNumber): string
    {
        return hash_hmac('sha256', $request->user()->id.':'.$cardNumber, (string) config('app.key'));
    }

    private function passesLuhn(string $cardNumber): bool
    {
        $sum = 0;
        $double = false;

        for ($index = strlen($cardNumber) - 1; $index >= 0; $index--) {
            $digit = (int) $cardNumber[$index];
            if ($double) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $double = ! $double;
        }

        return $sum % 10 === 0;
    }
}
