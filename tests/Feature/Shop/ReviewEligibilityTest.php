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

    public function test_a_customer_can_review_a_product_once_across_colors_and_orders(): void
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
        $this->assertSame(1, substr_count($ordersPage->getContent(), '>Review</a>'));
        $ordersPage
            ->assertSee('/images/red-racket.png')
            ->assertSee('/images/blue-racket.png')
            ->assertDontSee('/images/default-racket.png');

        $this->post(route('review.store', ['product' => $product->product_id]), [
            'order_no' => 'ORDER-NEWER',
            'star' => 5,
            'comment' => 'Great racket.',
        ])->assertRedirect();

        $this->assertDatabaseCount('reviews', 1);
        $this->get(route('orders'))
            ->assertOk()
            ->assertDontSee('>Review</a>', false);

        $this->get(route('review.create', [
            'product_id' => $product->product_id,
            'order_no' => 'ORDER-OLDER',
        ]))->assertRedirect(route('orders'));

        $this->from(route('orders'))
            ->post(route('review.store', ['product' => $product->product_id]), [
                'order_no' => 'ORDER-OLDER',
                'star' => 4,
                'comment' => 'Duplicate review.',
            ])
            ->assertRedirect(route('orders'))
            ->assertSessionHasErrors('order_no');

        $this->assertDatabaseCount('reviews', 1);
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
