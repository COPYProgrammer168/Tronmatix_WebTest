<?php

namespace Tests\Feature;

use App\Models\DeliveryProvider;
use App\Models\DeliveryProviderZone;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Verifies the checkout fee fix: OrderController::store() must resolve the
 * per-zone provider fee (delivery_provider_zones.fee) — NOT the legacy flat
 * `delivery_providers.fee` which the admin form stores as NULL — so `delivery`
 * is populated and `total` includes it, and the store() response carries the
 * provider details for the receipt.
 */
class OrderDeliveryFeeTest extends TestCase
{
    use RefreshDatabase;

    /** Create a customer directly (UserFactory references a missing column). */
    private function customer(): User
    {
        $email = 'customer-'.Str::random(8).'@test.local';
        return User::create([
            'name'     => 'Test Customer',
            'username' => 'customer-'.Str::random(8),
            'email'    => $email,
            'password' => 'password',
            'role'     => 'customer',
        ]);
    }

    private function provider(string $zone, ?float $fee, string $eta): DeliveryProvider
    {
        $p = DeliveryProvider::create([
            'name'       => 'Test Courier '.uniqid(),
            'is_active'  => true,
            'sort_order' => 0,
            'fee'        => null, // legacy flat column — always NULL (as admin sets it)
        ]);
        DeliveryProviderZone::create([
            'delivery_provider_id' => $p->id,
            'zone'                 => $zone,
            'fee'                  => $fee,
            'estimated_time'       => $eta,
        ]);
        return $p;
    }

    public function test_store_resolves_per_zone_fee_and_includes_it_in_total(): void
    {
        Sanctum::actingAs($this->customer(), ['*']);

        $product = Product::create([
            'name'     => 'Fee Test GPU',
            'price'    => '100.00',
            'category' => 'Components',
            'stock'    => 10,
        ]);

        $provider = $this->provider('phnom_penh', 1.25, '1-2 hours');

        $res = $this->postJson('/api/orders', [
            'items'             => [['product_id' => $product->id, 'qty' => 2]],
            'location'          => [
                'name'    => 'Test Buyer',
                'phone'   => '012345678',
                'address' => 'Street 160, Phnom Penh',
                'city'    => 'Phnom Penh',
            ],
            'payment_method'    => 'cash',
            'subtotal'          => 200.0,
            'fulfillment_type'  => 'delivery',
            'delivery_provider_id' => $provider->id,
            'delivery_zone'     => 'phnom_penh',
        ]);

        $res->assertStatus(201)
            ->assertJsonPath('delivery', 1.25)
            ->assertJsonPath('total', 201.25)
            ->assertJsonPath('delivery_provider_details.name', $provider->name)
            ->assertJsonPath('delivery_provider_details.fee', 1.25);

        $this->assertDatabaseHas('orders', [
            'id'                  => $res->json('id'),
            'delivery'            => 1.25,
            'total'               => 201.25,
            'delivery_provider_id' => $provider->id,
            'delivery_zone'       => 'phnom_penh',
        ]);
    }

    public function test_pickup_order_has_no_delivery_fee(): void
    {
        Sanctum::actingAs($this->customer(), ['*']);

        $product = Product::create([
            'name'     => 'Pickup GPU',
            'price'    => '50.00',
            'category' => 'Components',
            'stock'    => 5,
        ]);

        $provider = $this->provider('phnom_penh', 1.25, '1-2 hours');

        $res = $this->postJson('/api/orders', [
            'items'             => [['product_id' => $product->id, 'qty' => 1]],
            'location'          => [
                'name'    => 'Pickup Buyer',
                'phone'   => '012345678',
                'address' => 'Store Pickup',
            ],
            'payment_method'    => 'cash',
            'subtotal'          => 50.0,
            'fulfillment_type'  => 'pickup',
            'delivery_provider_id' => $provider->id,
            'delivery_zone'     => 'phnom_penh',
        ]);

        $res->assertStatus(201);
        $this->assertEquals(0, $res->json('delivery'));
        $this->assertEquals(50.0, $res->json('total'));
        $this->assertDatabaseHas('orders', [
            'id'       => $res->json('id'),
            'delivery' => 0.0,
            'total'    => 50.0,
        ]);
    }

    public function test_negotiable_fee_zone_saves_zero_delivery_but_keeps_provider(): void
    {
        Sanctum::actingAs($this->customer(), ['*']);

        $product = Product::create([
            'name'     => 'Province GPU',
            'price'    => '30.00',
            'category' => 'Components',
            'stock'    => 5,
        ]);

        // province zone has a NULL fee → negotiable
        $provider = $this->provider('province', null, 'Negotiable');

        $res = $this->postJson('/api/orders', [
            'items'             => [['product_id' => $product->id, 'qty' => 1]],
            'location'          => [
                'name'    => 'Province Buyer',
                'phone'   => '012345678',
                'address' => 'Kampong Speu',
                'city'    => 'Kampong Speu',
            ],
            'payment_method'    => 'cash',
            'subtotal'          => 30.0,
            'fulfillment_type'  => 'delivery',
            'delivery_provider_id' => $provider->id,
            'delivery_zone'     => 'province',
        ]);

        $res->assertStatus(201)
            ->assertJsonPath('delivery_provider_details.fee', null)
            ->assertJsonPath('delivery_provider_id', $provider->id);
        $this->assertEquals(0, $res->json('delivery'));
        $this->assertEquals(30.0, $res->json('total'));
        $this->assertDatabaseHas('orders', [
            'id'                  => $res->json('id'),
            'delivery'            => 0.0,
            'total'               => 30.0,
            'delivery_provider_id' => $provider->id,
        ]);
    }

    public function test_no_provider_selected_has_zero_fee(): void
    {
        Sanctum::actingAs($this->customer(), ['*']);

        $product = Product::create([
            'name'     => 'No Provider GPU',
            'price'    => '20.00',
            'category' => 'Components',
            'stock'    => 5,
        ]);

        $res = $this->postJson('/api/orders', [
            'items'            => [['product_id' => $product->id, 'qty' => 1]],
            'location'         => [
                'name'    => 'No Provider Buyer',
                'phone'   => '012345678',
                'address' => 'Somewhere',
            ],
            'payment_method'   => 'cash',
            'subtotal'         => 20.0,
            'fulfillment_type' => 'delivery',
            'delivery_zone'    => 'phnom_penh',
        ]);

        $res->assertStatus(201)
            ->assertJsonPath('delivery_provider_id', null);
        $this->assertEquals(0, $res->json('delivery'));
        $this->assertEquals(20.0, $res->json('total'));
        $this->assertDatabaseHas('orders', [
            'id'       => $res->json('id'),
            'delivery' => 0.0,
            'total'    => 20.0,
        ]);
    }
}
