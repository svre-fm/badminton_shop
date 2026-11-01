<?php

namespace Tests\Feature\Shop;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CheckoutSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_numbers_use_the_previous_random_format(): void
    {
        $user = User::factory()->create();
        $address = $user->Delivery_address()->create([
            'name' => 'Test Customer',
            'phone' => '0812345678',
            'address' => 'Test delivery address',
        ]);
        $productId = DB::table('products')->insertGetId([
            'name' => 'Training grip',
            'brand' => 'Test brand',
            'detail' => 'Test product',
            'price' => 100,
            'image' => '/images/test-grip.png',
            'product_type' => 'GRIP',
            'created_at' => now(),
            'updated_at' => now(),
        ], 'product_id');

        DB::table('productvariants')->insert([
            'product_id' => $productId,
            'color' => 'Black',
            'stock' => 5,
        ]);

        $this->actingAs($user);

        foreach ([1, 2] as $_) {
            DB::table('cart_items')->insert([
                'customer_id' => $user->id,
                'product_id' => $productId,
                'color' => 'Black',
                'racket_tension' => 0,
                'quantity' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $selectedItems = [[
                'product_id' => $productId,
                'color' => 'Black',
                'racket_tension' => 0,
            ]];

            $this->post(route('checkout.items'), ['items' => $selectedItems])
                ->assertRedirect(route('checkout'));
            $this->post(route('checkout.place'), [
                'delivery_id' => $address->delivery_id,
                'payment_method' => 'CASH_ON_DELIVERY',
            ])->assertRedirect(route('orders', ['status' => 'to_ship']));
        }

        $orderNumbers = DB::table('orders')->orderBy('order_no')->pluck('order_no')->all();
        $dateKey = now()->format('Ymd');

        $this->assertCount(2, $orderNumbers);
        $this->assertCount(2, array_unique($orderNumbers));
        foreach ($orderNumbers as $orderNumber) {
            $this->assertMatchesRegularExpression("/^ORD-{$dateKey}-[A-Z0-9]{10}$/", $orderNumber);
        }
    }

    public function test_same_product_and_color_can_be_ordered_at_different_tensions(): void
    {
        $user = User::factory()->create();
        $address = $user->Delivery_address()->create([
            'name' => 'Test Customer',
            'phone' => '0812345678',
            'address' => 'Test delivery address',
        ]);
        $productId = DB::table('products')->insertGetId([
            'name' => 'Tension racket',
            'brand' => 'Test brand',
            'detail' => 'Test product',
            'price' => 1000,
            'image' => '/images/test-racket.png',
            'product_type' => 'RACKET',
            'created_at' => now(),
            'updated_at' => now(),
        ], 'product_id');

        DB::table('productvariants')->insert([
            'product_id' => $productId,
            'color' => 'Black/Cherry',
            'stock' => 5,
        ]);

        foreach ([18, 20] as $tension) {
            DB::table('cart_items')->insert([
                'customer_id' => $user->id,
                'product_id' => $productId,
                'color' => 'Black/Cherry',
                'racket_tension' => $tension,
                'quantity' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $selectedItems = collect([18, 20])->map(fn ($tension) => [
            'product_id' => $productId,
            'color' => 'Black/Cherry',
            'racket_tension' => $tension,
        ])->all();

        $this->actingAs($user)
            ->post(route('checkout.items'), ['items' => $selectedItems])
            ->assertRedirect(route('checkout'));

        $this->post(route('checkout.place'), [
            'delivery_id' => $address->delivery_id,
            'payment_method' => 'CASH_ON_DELIVERY',
        ])->assertRedirect(route('orders', ['status' => 'to_ship']));
        $this->assertFalse(session()->has('status'));

        $orderNo = DB::table('orders')->value('order_no');

        $this->assertSame(
            [18, 20],
            DB::table('order_items')
                ->where('order_no', $orderNo)
                ->orderBy('tension')
                ->pluck('tension')
                ->map(fn ($tension) => (int) $tension)
                ->all(),
        );
        $this->assertSame(3, DB::table('productvariants')->where('product_id', $productId)->value('stock'));
        $this->assertDatabaseMissing('cart_items', ['customer_id' => $user->id, 'product_id' => $productId]);
    }

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
                'racket_tension' => 0,
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
        $this->assertFalse(session()->has('status'));

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
