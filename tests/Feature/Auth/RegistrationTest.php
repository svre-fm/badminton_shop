<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::registration());
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('home', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_new_users_return_to_the_requested_page_after_registration(): void
    {
        $destination = route('shop.index', absolute: false);

        $this->get(route('register', ['return_to' => $destination]))
            ->assertOk();

        $this->post(route('register.store'), [
            'name' => 'John Doe',
            'email' => 'return@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasNoErrors()
            ->assertRedirect($destination);

        $this->assertAuthenticated();
    }
}
