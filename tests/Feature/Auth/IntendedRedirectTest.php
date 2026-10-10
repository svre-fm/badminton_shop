<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class IntendedRedirectTest extends TestCase
{
    public function test_login_page_remembers_a_same_site_return_destination(): void
    {
        $destination = '/shop?category=grip';

        $this->get(route('login', ['return_to' => $destination]))
            ->assertOk()
            ->assertSessionHas('url.intended', $destination);
    }

    public function test_register_page_remembers_a_same_site_return_destination(): void
    {
        $destination = '/product/racket';

        $this->get(route('register', ['return_to' => $destination]))
            ->assertOk()
            ->assertSessionHas('url.intended', $destination);
    }

    public function test_external_return_destination_is_ignored(): void
    {
        $this->get(route('login', ['return_to' => 'https://example.com']))
            ->assertOk()
            ->assertSessionMissing('url.intended');
    }

    public function test_guest_access_to_cart_remembers_the_cart_as_the_return_destination(): void
    {
        $this->get(route('cart'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('url.intended', route('cart'));
    }

    public function test_guest_cart_link_from_a_product_returns_to_that_product_after_login(): void
    {
        $productUrl = route('shop.product', ['category' => 'badminton-racket', 'slug' => 'sample'], false);

        $this->get($productUrl)
            ->assertOk()
            ->assertSee(route('login', ['return_to' => $productUrl]), false);
    }
}
