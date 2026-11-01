<?php

namespace App\Http\Controllers;

use App\Models\products;
use App\Models\reviews;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function create(Request $request): RedirectResponse|View
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,product_id'],
            'order_no' => ['required', 'string', 'max:255'],
        ]);

        $productId = (int) $validated['product_id'];
        $product = products::query()->where('product_id', $productId)->firstOrFail();
        $order = $request->user()->orders()
            ->where('order_no', $validated['order_no'])
            ->where('status', 'Complete')
            ->whereHas('items', fn ($query) => $query->where('product_id', $productId))
            ->firstOrFail();

        if ($request->user()->reviews()->where('product_id', $productId)->exists()) {
            return redirect()->route('orders')
                ->with('status', 'You have already reviewed this product.');
        }

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
        $productId = (int) $product->getKey();

        DB::transaction(function () use ($user, $productId, $validated): void {
            $lockedProduct = products::query()
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->firstOrFail();

            $order = $user->orders()
                ->where('order_no', $validated['order_no'])
                ->where('status', 'Complete')
                ->whereHas('items', fn ($query) => $query->where('product_id', $productId))
                ->first();

            if ($order === null) {
                abort(403, 'You can only review products from your completed orders.');
            }

            if ($user->reviews()->where('product_id', $productId)->exists()) {
                throw ValidationException::withMessages([
                    'order_no' => 'You have already reviewed this product.',
                ]);
            }

            $lockedProduct->reviews()->create([
                'customer_id' => $user->id,
                'order_no' => $validated['order_no'],
                'star' => $validated['star'],
                'date' => now()->toDateString(),
                'comment' => $validated['comment'] ?? null,
            ]);
        });

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
