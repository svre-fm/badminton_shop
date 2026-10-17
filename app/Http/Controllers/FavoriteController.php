<?php

namespace App\Http\Controllers;

use App\Models\products;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $favorites = $request->user()
            ->favorites()
            ->withAvg('reviews', 'star')
            ->orderBy('products.product_id')
            ->paginate(20);

        $favorites->getCollection()->transform(function (products $product) {
            $path = $product->image;
            $product->image = $path
                && ! str_starts_with($path, 'http://')
                && ! str_starts_with($path, 'https://')
                && ! str_starts_with($path, '/')
                ? asset($path)
                : $path;

            return $product;
        });

        return view('shop.favorites', compact('favorites'));
    }

    /**
     * กดหัวใจ: ถ้ายังไม่มีในรายการโปรดให้เพิ่ม ถ้ามีแล้วให้ลบ
     * ตาราง favorites มีคอลัมน์ customer_id, product_id (customer_id = users.id)
     */
    public function toggle(Request $request, products $product): RedirectResponse
    {
        $validated = $request->validate([
            'favorited' => ['required', 'boolean'],
        ]);

        $favorites = $request->user()->favorites();
        if ($validated['favorited']) {
            $favorites->syncWithoutDetaching([$product->product_id]);
        } else {
            $favorites->detach($product->product_id);
        }

        return back();
    }
}