<?php

namespace Tests\Feature\Shop;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AddressManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_addresses_can_be_created_updated_and_deleted_without_leaving_checkout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->postJson(route('address.store'), [
            'name' => 'First Recipient',
            'phone' => '0812345678',
            'address' => 'First delivery address',
        ])->assertCreated()
            ->assertJsonPath('address.name', 'First Recipient')
            ->assertJsonPath('address.phone', '0812345678')
            ->assertJsonPath('address.address', 'First delivery address');

        $addressId = $user->Delivery_address()->value('delivery_id');

        $this->putJson(route('address.update', ['address' => $addressId]), [
            'name' => 'Updated Recipient',
            'phone' => '0898765432',
            'address' => 'Updated delivery address',
        ])->assertOk()
            ->assertJsonPath('address.delivery_id', $addressId)
            ->assertJsonPath('address.name', 'Updated Recipient')
            ->assertJsonPath('address.phone', '0898765432')
            ->assertJsonPath('address.address', 'Updated delivery address');

        $this->deleteJson(route('address.destroy', ['address' => $addressId]))
            ->assertOk()
            ->assertJsonPath('deleted', true);

        $this->assertDatabaseMissing('delivery_addresses', ['delivery_id' => $addressId]);
    }

    public function test_address_phone_accepts_only_up_to_ten_digits_on_create_and_update(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        foreach (['08123abc45', '08123456789'] as $phone) {
            $this->postJson(route('address.store'), [
                'name' => 'Recipient',
                'phone' => $phone,
                'address' => 'Delivery address',
            ])->assertUnprocessable()
                ->assertJsonValidationErrors('phone');
        }

        $addressId = $user->Delivery_address()->create([
            'name' => 'Recipient',
            'phone' => '0812345678',
            'address' => 'Delivery address',
        ])->delivery_id;

        foreach (['08123abc45', '08123456789'] as $phone) {
            $this->putJson(route('address.update', ['address' => $addressId]), [
                'name' => 'Recipient',
                'phone' => $phone,
                'address' => 'Delivery address',
            ])->assertUnprocessable()
                ->assertJsonValidationErrors('phone');
        }
    }

    public function test_checkout_address_drawer_can_edit_addresses_without_a_page_refresh(): void
    {
        $user = User::factory()->create();
        $productId = DB::table('products')->insertGetId([
            'name' => 'Checkout product',
            'brand' => 'Test brand',
            'detail' => 'Test product',
            'price' => 100,
            'image' => '/images/test-product.png',
            'product_type' => 'GRIP',
            'created_at' => now(),
            'updated_at' => now(),
        ], 'product_id');

        DB::table('productvariants')->insert([
            'product_id' => $productId,
            'color' => 'Black',
            'stock' => 5,
        ]);
        DB::table('cart_items')->insert([
            'customer_id' => $user->id,
            'product_id' => $productId,
            'color' => 'Black',
            'quantity' => 1,
            'racket_tension' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user);

        $this->get(route('checkout'))
            ->assertOk()
            ->assertSee('openNewAddressForm()', false)
            ->assertSee('openEditAddressForm(delivery_address)', false)
            ->assertSee('saveAddress()', false)
            ->assertSee('this.delivery_addresses.splice(index, 1, savedAddress)', false)
            ->assertSee('slice(0, 10)', false);
    }
}
