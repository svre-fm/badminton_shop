<?php

namespace Tests\Feature\Shop;

use App\Models\products;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReviewEligibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_customer_can_edit_and_delete_only_their_own_review(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $product = products::create([
            'name' => 'Reviewable grip',
            'brand' => 'Test brand',
            'detail' => 'Test product',
            'price' => 100,
            'image' => '/images/grip.png',
            'product_type' => 'GRIP',
        ]);
        DB::table('productvariants')->insert([
            'product_id' => $product->product_id,
            'color' => 'Black',
            'stock' => 5,
        ]);
        $this->createCompletedOrder($user, 'ORDER-REVIEW', $product, ['Black'], '2026-10-10');
        $reviewId = DB::table('reviews')->insertGetId([
            'customer_id' => $user->id,
            'product_id' => $product->product_id,
            'order_no' => 'ORDER-REVIEW',
            'star' => 3,
            'date' => '2026-10-20',
            'comment' => 'Original review',
        ], 'no');

        $this->actingAs($user)
            ->get(route('review.edit', ['product' => $product->product_id, 'review' => $reviewId]))
            ->assertOk()
            ->assertSee('Edit your review')
            ->assertSee('Original review');

        $this->get(route('shop.product', ['category' => 'grip', 'product_id' => $product->product_id]))
            ->assertOk()
            ->assertSee('aria-label="Edit your review"', false)
            ->assertSee('aria-label="Delete your review"', false);

        $this->put(route('review.update', ['product' => $product->product_id, 'review' => $reviewId]), [
            'star' => 5,
            'comment' => 'Updated review',
        ])->assertRedirect()
            ->assertSessionHas('toast.text', 'Your review has been updated.');

        $this->assertDatabaseHas('reviews', [
            'no' => $reviewId,
            'customer_id' => $user->id,
            'star' => 5,
            'comment' => 'Updated review',
        ]);

        $this->actingAs($otherUser);
        $this->get(route('shop.product', ['category' => 'grip', 'product_id' => $product->product_id]))
            ->assertOk()
            ->assertDontSee('aria-label="Edit your review"', false)
            ->assertDontSee('aria-label="Delete your review"', false);
        $this->get(route('review.edit', ['product' => $product->product_id, 'review' => $reviewId]))
            ->assertNotFound();
        $this->put(route('review.update', ['product' => $product->product_id, 'review' => $reviewId]), [
            'star' => 1,
            'comment' => 'Not owner',
        ])->assertNotFound();
        $this->delete(route('review.destroy', ['product' => $product->product_id, 'review' => $reviewId]))
            ->assertNotFound();

        $this->actingAs($user)
            ->delete(route('review.destroy', ['product' => $product->product_id, 'review' => $reviewId]))
            ->assertRedirect()
            ->assertSessionHas('toast.text', 'Your review has been deleted.');

        $this->assertDatabaseMissing('reviews', ['no' => $reviewId]);
    }

    public function test_a_customer_can_review_a_product_once_per_order_across_colors(): void
    {
        $user = User::factory()->create();
        $product = products::create([
            'name' => 'Colorful racket',
            'brand' => 'Test brand',
            'detail' => 'Test product',
            'price' => 1000,
            'image' => '/images/default-racket.png',
            'product_type' => 'RACKET',
        ]);

        foreach ([
            'Red' => '/images/red-racket.png',
            'Blue' => '/images/blue-racket.png',
        ] as $color => $image) {
            DB::table('productvariants')->insert([
                'product_id' => $product->product_id,
                'color' => $color,
                'image' => $image,
                'stock' => 5,
            ]);
        }

        $this->createCompletedOrder($user, 'ORDER-OLDER', $product, ['Red', 'Blue'], '2026-10-10');
        $this->createCompletedOrder($user, 'ORDER-NEWER', $product, ['Red'], '2026-10-20');

        $this->actingAs($user);

        $ordersPage = $this->get(route('orders'))->assertOk();
        $this->assertSame(2, substr_count($ordersPage->getContent(), '>Review</a>'));
        $ordersPage
            ->assertSee('/images/red-racket.png')
            ->assertSee('/images/blue-racket.png')
            ->assertDontSee('/images/default-racket.png');

        $this->post(route('review.store', ['product' => $product->product_id]), [
            'order_no' => 'ORDER-NEWER',
            'star' => 5,
            'comment' => 'Great racket.',
        ])->assertRedirect()
            ->assertSessionHas('toast', [
                'variant' => 'success',
                'text' => 'Your review has been saved.',
            ]);

        $this->assertDatabaseCount('reviews', 1);
        $this->get(route('shop.product', [
            'category' => 'badminton-racket',
            'product_id' => $product->product_id,
        ]))
            ->assertOk()
            ->assertSee('toast-show', false)
            ->assertSee('Your review has been saved.')
            ->assertDontSee('rounded-lg border border-emerald-200 bg-emerald-50', false);

        $this->get(route('orders'))
            ->assertOk()
            ->assertSee('ORDER-OLDER')
            ->assertSee('>Review</a>', false);

        $this->get(route('review.create', [
            'product_id' => $product->product_id,
            'order_no' => 'ORDER-OLDER',
        ]))->assertOk()
            ->assertSee('Colorful racket');

        $this->post(route('review.store', ['product' => $product->product_id]), [
            'order_no' => 'ORDER-OLDER',
            'star' => 4,
            'comment' => 'Still great in a new order.',
        ])->assertRedirect();

        $this->from(route('orders'))
            ->post(route('review.store', ['product' => $product->product_id]), [
                'order_no' => 'ORDER-OLDER',
                'star' => 4,
                'comment' => 'Duplicate review.',
            ])
            ->assertRedirect(route('orders'))
            ->assertSessionHasErrors('order_no');

        $this->assertDatabaseCount('reviews', 2);
        $this->get(route('orders'))
            ->assertOk()
            ->assertDontSee('>Review</a>', false);
    }

    private function createCompletedOrder(
        User $user,
        string $orderNo,
        products $product,
        array $colors,
        string $date,
    ): void {
        DB::table('orders')->insert([
            'order_no' => $orderNo,
            'customer_id' => $user->id,
            'status' => 'Complete',
            'order_date' => $date,
            'delivery_id' => null,
            'total_price' => count($colors) * 1000,
        ]);

        foreach ($colors as $color) {
            DB::table('order_items')->insert([
                'order_no' => $orderNo,
                'product_id' => $product->product_id,
                'color' => $color,
                'quantity' => 1,
                'unit_price' => 1000,
                'tension' => null,
            ]);
        }
    }
}
