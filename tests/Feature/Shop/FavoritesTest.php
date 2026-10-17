<?php

namespace Tests\Feature\Shop;

use App\Models\products;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoritesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_save_a_product_as_favorite(): void
    {
        $product = $this->createProduct();

        $this->post(route('favorites.toggle', ['product' => $product->product_id]), [
            'favorited' => true,
        ])->assertRedirect(route('login'));

        $this->assertDatabaseMissing('favorites', ['product_id' => $product->product_id]);
    }

    public function test_member_can_save_and_remove_a_product_and_see_it_on_favorites_page(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct();
        $this->actingAs($user);

        $this->from(route('favorites'))
            ->post(route('favorites.toggle', ['product' => $product->product_id]), ['favorited' => true])
            ->assertRedirect(route('favorites'));

        $this->assertDatabaseHas('favorites', [
            'customer_id' => $user->id,
            'product_id' => $product->product_id,
        ]);

        $this->get(route('favorites'))
            ->assertOk()
            ->assertSee('Training racket')
            ->assertSee('aria-pressed="true"', false)
            ->assertSee('name="favorited" value="0"', false);

        $this->from(route('favorites'))
            ->post(route('favorites.toggle', ['product' => $product->product_id]), ['favorited' => false])
            ->assertRedirect(route('favorites'));

        $this->assertDatabaseMissing('favorites', [
            'customer_id' => $user->id,
            'product_id' => $product->product_id,
        ]);

        $this->get(route('favorites'))
            ->assertOk()
            ->assertSee('No favorites yet.')
            ->assertDontSee('Training racket');
    }

    public function test_favorites_are_private_to_each_member(): void
    {
        $owner = User::factory()->create();
        $otherMember = User::factory()->create();
        $product = $this->createProduct();

        $this->actingAs($owner)
            ->post(route('favorites.toggle', ['product' => $product->product_id]), ['favorited' => true]);

        $this->actingAs($otherMember)
            ->get(route('favorites'))
            ->assertOk()
            ->assertSee('No favorites yet.')
            ->assertDontSee('Training racket');
    }

    private function createProduct(): products
    {
        return products::create([
            'name' => 'Training racket',
            'brand' => 'Test brand',
            'detail' => 'Test product',
            'price' => 1200,
            'image' => 'imgs/test-racket.png',
            'product_type' => 'RACKET',
        ]);
    }
}
