<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()->Delivery_address()->get();

        return view('addresses.index', compact('addresses'));
    }

    public function create()
    {
        return view('shop.address-form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:2000'],
        ]);

        $address = $request->user()->Delivery_address()->create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'address' => [
                    'delivery_id' => $address->delivery_id,
                    'name' => $address->name,
                    'phone' => $address->phone,
                    'address' => $address->address,
                ],
            ], 201);
        }

        return redirect()->route('checkout')->with('status', 'Delivery address saved.');
    }

    public function edit(Request $request, int $address)
    {
        $address = $request->user()->Delivery_address()->findOrFail($address);

        $delivery_address = $address;

        return view('shop.address-form', compact('delivery_address'));
    }

    public function update(Request $request, int $address)
    {
        $address = $request->user()->Delivery_address()->findOrFail($address);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:2000'],
        ]);
        $address->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'address' => [
                    'delivery_id' => $address->delivery_id,
                    'name' => $address->name,
                    'phone' => $address->phone,
                    'address' => $address->address,
                ],
            ]);
        }

        return redirect()->route('checkout')->with('status', 'Address updated.');
    }

    public function destroy(Request $request, int $address)
    {
        $request->user()->Delivery_address()->findOrFail($address)->delete();

        if ($request->expectsJson()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()->route('checkout')->with('status', 'Address deleted.');
    }
}
