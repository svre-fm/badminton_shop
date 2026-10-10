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
            ->assertSee('Set as default address');

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
            ->assertSee('Checkout preview only', false)
            ->assertSee('My Address')
            ->assertSee('Add new address')
            ->assertSee('Save')
            ->assertSee('Delete this address', false)
            ->assertSee('cursor-pointer', false)
            ->assertSee('removeAddress(address.id)', false)
            ->assertSee('max-w-screen-2xl', false)
            ->assertSee('Order Summary')
            ->assertSee('lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]', false);

        $this->get(route('shop.product', ['category' => 'badminton-racket', 'slug' => 'badminton-racket-1']))
            ->assertOk()
            ->assertSee('Add to cart')
            ->assertSee('Buy');
    }

    public function test_order_status_page_shows_review_and_order_expansion_controls(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('orders'))
            ->assertOk()
            ->assertSee('Status Delivery')
            ->assertSee('ORD-20261003-0001')
            ->assertSee('Review')
            ->assertSee('View More')
            ->assertSee('Total in 2 items');
    }

    public function test_favorites_pagination_uses_subtle_interactive_chevrons(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('favorites'))
            ->assertOk()
            ->assertSee('Racket 1')
            ->assertDontSee('Racket 5')
            ->assertSee('Previous page')
            ->assertSee('Next page')
            ->assertSee('aria-disabled="true"', false)
            ->assertSee('page=2')
            ->assertSee('aria-current="page"', false)
            ->assertSee('hover:border-brand', false)
            ->assertSee('size-7', false);
    }

    public function test_favorites_pagination_changes_items_and_active_page(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('favorites', ['page' => 2]))
            ->assertOk()
            ->assertSee('Racket 5')
            ->assertDontSee('Racket 1')
            ->assertSee('page=1')
            ->assertSee('page=3')
            ->assertSee('<span class="grid size-7 place-items-center rounded-full bg-ink font-bold text-white" aria-current="page">2</span>', false);
    }

    public function test_favorites_pagination_clamps_invalid_page_numbers(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('favorites', ['page' => 99]))
            ->assertOk()
            ->assertSee('Racket 13')
            ->assertSee('Racket 16')
            ->assertSee('<span class="grid size-7 place-items-center rounded-full bg-ink font-bold text-white" aria-current="page">4</span>', false)
            ->assertSee('aria-disabled="true"', false);
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
}
