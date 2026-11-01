<?php

namespace Tests\Feature\Shop;

use App\Models\User;
use Database\Seeders\Support\SeedHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CardSaveFeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_a_card_redirects_without_a_success_flash_message(): void
    {
        $user = User::factory()->create();
        $cardNumber = SeedHelper::cardNumber();

        $this->actingAs($user)
            ->post(route('payment-method.store'), [
                'card_no' => $cardNumber,
                'expiry_date' => '2099-12',
            ])
            ->assertRedirect(route('checkout'));

        $this->assertDatabaseHas('credit_debit_cards', [
            'user_id' => $user->id,
            'card_no' => $cardNumber,
        ]);
        $this->assertFalse(session()->has('status'));
    }
}
