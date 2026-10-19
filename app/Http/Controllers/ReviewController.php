<?php

namespace App\Http\Controllers;

use App\Models\products;
use App\Models\reviews;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function create(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,product_id'],
            'order_no' => ['required', 'string', 'max:255'],
        ]);

        $product = products::query()->findOrFail($validated['product_id']);
        $order = $request->user()->orders()
            ->where('order_no', $validated['order_no'])
            ->where('status', 'Complete')
            ->whereHas('items', fn ($query) => $query->where('product_id', $product->product_id))
            ->firstOrFail();

        return view('shop.review', compact('product', 'order'));
    }

    public function store(Request $request, products $product): RedirectResponse
    {
        $validated = $request->validate([
            'order_no' => ['required', 'string', 'max:255'],
            'star' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:5000'],
        ]);

        $user = $request->user();
        $order = $user->orders()
            ->where('order_no', $validated['order_no'])
            ->where('status', 'Complete')
            ->whereHas('items', fn ($query) => $query->where('product_id', $product->product_id))
            ->first();

        abort_unless($order, 403, 'You can only review products from your completed orders.');

        $alreadyReviewed = DB::table('reviews')
            ->where('customer_id', $user->id)
            ->where('product_id', $product->product_id)
            ->where('order_no', $order->order_no)
            ->exists();

        if ($alreadyReviewed) {
            throw ValidationException::withMessages([
                'order_no' => 'You have already reviewed this product for this order.',
            ]);
        }

        $product->reviews()->create([
            'customer_id' => $user->id,
            'order_no' => $order->order_no,
            'star' => $validated['star'],
            'date' => now()->toDateString(),
            'comment' => $validated['comment'] ?? null,
        ]);

        return redirect()
            ->route('shop.product', ['category' => $this->categorySlug($product->product_type), 'product_id' => $product->product_id])
            ->withFragment('reviews')
            ->with('status', 'Your review has been saved.');
    }

    public function destroy(Request $request, products $product, int $review): RedirectResponse
    {
        $review = reviews::query()
            ->where('no', $review)
            ->where('product_id', $product->product_id)
            ->where('customer_id', $request->user()->id)
            ->firstOrFail();

        $review->delete();

        return redirect()
            ->route('shop.product', ['category' => $this->categorySlug($product->product_type), 'product_id' => $product->product_id])
            ->withFragment('reviews')
            ->with('status', 'Your review has been deleted.');
    }

    private function categorySlug(string $type): string
    {
        return match ($type) {
            'STRING' => 'badminton-string',
            'GRIP' => 'grip',
            'SHUTTLECOCK' => 'shuttlecock',
            default => 'badminton-racket',
        };
    }
}
