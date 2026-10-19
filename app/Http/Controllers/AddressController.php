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

        $request->user()->Delivery_address()->create($validated);

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

        return redirect()->route('checkout')->with('status', 'Address updated.');
    }

    public function destroy(Request $request, int $address)
    {
        $request->user()->Delivery_address()->findOrFail($address)->delete();

        return redirect()->route('checkout')->with('status', 'Address deleted.');
    }
}
