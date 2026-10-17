<?php

namespace Tests\Feature\Shop;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopFormPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_member_form_pages(): void
    {
        $this->get(route('address.create'))->assertRedirect(route('login'));
        $this->get(route('address.edit', ['id' => 1]))->assertRedirect(route('login'));
        $this->get(route('payment-method.create'))->assertRedirect(route('login'));
        $this->get(route('review.create'))->assertRedirect(route('login'));
    }

    public function test_member_form_pages_render_the_expected_forms(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('address.create'))
            ->assertOk()
            ->assertSee('Add your address')
            ->assertSee('name="address"', false)
            ->assertDontSee('Set as default address');

        $this->get(route('address.edit', ['id' => 12]))
            ->assertOk()
            ->assertSee('Edit your address')
            ->assertSee('Address #12');

        $this->get(route('payment-method.create'))
            ->assertOk()
            ->assertSee('Add Credit Card')
            ->assertSee('Do not enter real card details');

        $this->get(route('review.create', ['product' => 'Badminton racket']))
            ->assertOk()
            ->assertSee('Write your review')
            ->assertSee('Badminton racket')
            ->assertSee('Your rating');
    }

    public function test_member_cart_and_checkout_pages_render(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('cart'))
            ->assertOk()
            ->assertSee('My cart')
            ->assertSee('Purchase');

        $this->get(route('checkout'))
            ->assertOk()
            ->assertSee('Product Order')
            ->assertSee('Place Order')
            ->assertSee('My Address')
            ->assertSee('Add new address')
            ->assertSee('Save')
            ->assertSee('Delete this address', false)
            ->assertSee('cursor-pointer', false)
            ->assertSee('removeAddress(delivery_address.id)', false)
            ->assertDontSee('Credit Card 1')
            ->assertSee('max-w-screen-2xl', false)
            ->assertSee('Order Summary')
            ->assertSee('lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]', false);

        $this->get(route('shop.product', ['category' => 'badminton-racket', 'product_id' => 1]))
            ->assertOk()
            ->assertSee('Product information is not available yet.')
            ->assertDontSee('Badminton racket-1')
            ->assertDontSee('Review text goes here.');
    }

    public function test_product_page_uses_controller_data_for_tension_and_review_filtering(): void
    {
        $this->view('shop.product', [
            'product' => [
                'name' => 'Club racket',
                'product_id' => 1,
                'product_type' => 'RACKET',
                'price' => 1800,
                'rating' => 4.5,
                'image' => '/images/club-racket.jpg',
                'images' => [],
                'product_variants' => [
                    ['color' => 'Black', 'stock' => 5],
                    ['color' => 'Black/Cherry', 'stock' => 2],
                ],
                'description' => 'Product description',
            ],
            'max_tension' => 28,
            'reviews' => collect([
                ['name' => 'Reviewer', 'date' => 'Today', 'star' => 3, 'comment' => 'Review content'],
            ]),
        ])
            ->assertSee('Club racket')
            ->assertSee('min="18"', false)
            ->assertSee('max="28"', false)
            ->assertSee('background: linear-gradient(90deg, #000000 0% 50%, #8b1e3f 50% 100%)', false)
            ->assertSee('Enter a value from 18 to 28 lbs')
            ->assertSee('3 star (1)')
            ->assertSee('ratingFilter === 3', false)
            ->assertSee('Review content');
    }

    public function test_order_status_page_shows_empty_state_without_controller_data(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('orders'))
            ->assertOk()
            ->assertSee('Status Delivery')
            ->assertSee('Your order history will appear here when you place an order.')
            ->assertDontSee('ORD-20261003-0001')
            ->assertDontSee('Review text goes here.');
    }

    public function test_favorites_page_shows_empty_state_without_controller_data(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('favorites'))
            ->assertOk()
            ->assertSee('No favorites yet.')
            ->assertDontSee('Racket 1')
            ->assertDontSee('Previous page');
    }

    public function test_shop_category_page_does_not_show_a_separate_category_selector(): void
    {
        $this->get(route('shop.index', ['category' => 'badminton-racket']))
            ->assertOk()
            ->assertDontSee('id="category"', false)
            ->assertDontSee('Product category')
            ->assertSee('shop-title', false)
            ->assertSee("group.open !== false ? 'rotate-90' : ''", false)
            ->assertSee('transition-transform duration-200', false)
            ->assertSee('id="sort-trigger"', false)
            ->assertSee('aria-haspopup="listbox"', false)
            ->assertSee("sortOpen ? 'rotate-90' : ''", false)
            ->assertSee('Sort by');
    }

    public function test_home_and_catalog_do_not_fall_back_to_sample_products(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Badminton racket')
            ->assertSee('Badminton string')
            ->assertSee('Grip')
            ->assertSee('Shuttlecock')
            ->assertSee('badminton-racket-icon.png')
            ->assertSee('badminton-string-icon.png')
            ->assertSee('grip-icon.png')
            ->assertSee('shuttlecock-icon.png')
            ->assertSee(route('shop.index', ['category' => 'badminton-racket']), false)
            ->assertDontSee('Racket 1');

        $this->get(route('shop.index', ['category' => 'badminton-racket']))
            ->assertOk()
            ->assertSee('No products are available in this category yet.')
            ->assertDontSee('Yonex')
            ->assertDontSee('Badminton racket 01');
    }
}
