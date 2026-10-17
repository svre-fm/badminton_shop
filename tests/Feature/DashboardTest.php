<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_legacy_dashboard_url_redirects_to_store_home(): void
    {
        $this->get('/dashboard')
            ->assertRedirect(route('home'));
    }

    public function test_root_url_redirects_to_canonical_store_home(): void
    {
        $this->get('/')
            ->assertRedirect(route('home'));
    }

    public function test_legacy_shop_and_product_urls_redirect_to_canonical_paths(): void
    {
        $this->get('/shop?category=grip')
            ->assertRedirect('/category/grip');

        $this->get('/product/sample')
            ->assertRedirect('/product/badminton-racket/sample');
    }
}
