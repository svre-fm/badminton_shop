<?php

namespace Tests\Feature\Shop;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CheckoutSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_selected_cart_items_are_checked_out_and_ordered(): void
    {
        $user = User::factory()->create();
        $address = $user->Delivery_address()->create([
            'name' => 'Test Customer',
            'phone' => '0812345678',
            'address' => 'Test delivery address',
        ]);
        $cartItems = [];

        foreach (range(1, 5) as $number) {
            $productId = DB::table('products')->insertGetId([
                'name' => "Product {$number}",
                'brand' => 'Test brand',
                'detail' => 'Test product',
                'price' => 100 * $number,
                'image' => '/images/test-product.png',
                'product_type' => 'GRIP',
                'created_at' => now(),
                'updated_at' => now(),
            ], 'product_id');

            DB::table('productvariants')->insert([
                'product_id' => $productId,
                'color' => "Color {$number}",
                'stock' => 5,
            ]);

            DB::table('cart_items')->insert([
                'customer_id' => $user->id,
                'product_id' => $productId,
                'color' => "Color {$number}",
                'quantity' => 1,
                'racket_tension' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $cartItems[] = [
                'product_id' => $productId,
                'color' => "Color {$number}",
            ];
        }

        $selectedItems = [$cartItems[1], $cartItems[3]];

        $this->actingAs($user)
            ->post(route('checkout.items'), ['items' => $selectedItems])
            ->assertRedirect(route('checkout'))
            ->assertSessionHas('checkout.cart_items', $selectedItems);

        $this->get(route('checkout'))
            ->assertOk()
            ->assertSee('Product 2')
            ->assertSee('Product 4')
            ->assertDontSee('Product 1')
            ->assertDontSee('Product 3')
            ->assertDontSee('Product 5');

        $this->post(route('checkout.place'), [
            'delivery_id' => $address->delivery_id,
            'payment_method' => 'CASH_ON_DELIVERY',
        ])->assertRedirect(route('orders', ['status' => 'to_ship']));

        $orderNo = DB::table('orders')->value('order_no');

        $this->assertSame(
            collect($selectedItems)->pluck('product_id')->sort()->values()->all(),
            DB::table('order_items')
                ->where('order_no', $orderNo)
                ->orderBy('product_id')
                ->pluck('product_id')
                ->all(),
        );
        $this->assertSame(3, DB::table('cart_items')->where('customer_id', $user->id)->count());

        foreach (collect($selectedItems)->pluck('product_id') as $productId) {
            $this->assertSame(4, DB::table('productvariants')->where('product_id', $productId)->value('stock'));
            $this->assertDatabaseMissing('cart_items', [
                'customer_id' => $user->id,
                'product_id' => $productId,
            ]);
        }

        foreach (collect($cartItems)->pluck('product_id')->diff(collect($selectedItems)->pluck('product_id')) as $productId) {
            $this->assertSame(5, DB::table('productvariants')->where('product_id', $productId)->value('stock'));
            $this->assertDatabaseHas('cart_items', [
                'customer_id' => $user->id,
                'product_id' => $productId,
            ]);
        }

        $this->assertFalse(session()->has('checkout.cart_items'));
    }
}
