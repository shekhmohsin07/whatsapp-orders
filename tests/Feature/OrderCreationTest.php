<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderCreationTest extends TestCase
{
    use RefreshDatabase;

    private function makeStore(User $owner, string $name = 'ABC Restaurant', string $status = 'active'): Store
    {
        return $owner->stores()->create([
            'name' => $name,
            'slug' => Store::generateUniqueSlug($name),
            'whatsapp_number' => '+15551234567',
            'currency' => '$',
            'status' => $status,
        ]);
    }

    private function makeProduct(Store $store, string $name, float $price, string $status = 'active'): Product
    {
        return $store->products()->create([
            'name' => $name,
            'slug' => Product::generateUniqueSlug($store->id, $name),
            'price' => $price,
            'status' => $status,
        ]);
    }

    private function payload(array $items): array
    {
        return [
            'customer_name' => 'John',
            'customer_phone' => '+123456789',
            'customer_address' => '123 Main Street',
            'items' => $items,
        ];
    }

    public function test_order_uses_database_prices_and_returns_whatsapp_url(): void
    {
        $store = $this->makeStore(User::factory()->create());
        $burger = $this->makeProduct($store, 'Chicken Burger', 5);
        $coke = $this->makeProduct($store, 'Coke', 2);

        // The client tries to forge prices. They must be ignored.
        $response = $this->postJson('/api/public/stores/abc-restaurant/orders', $this->payload([
            ['product_id' => $burger->id, 'quantity' => 2, 'price' => 0.01],
            ['product_id' => $coke->id, 'quantity' => 1, 'price' => 0.01],
        ]));

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Order created successfully')
            ->assertJsonStructure(['success', 'message', 'order' => ['id', 'items'], 'whatsapp_url']);

        $this->assertDatabaseHas('orders', [
            'store_id' => $store->id,
            'total_amount' => 12,
            'status' => 'pending',
        ]);
        $this->assertDatabaseCount('order_items', 2);
        $this->assertDatabaseHas('order_items', [
            'product_name' => 'Chicken Burger',
            'quantity' => 2,
            'price' => 5,
            'subtotal' => 10,
        ]);

        $url = $response->json('whatsapp_url');
        $this->assertStringStartsWith('https://wa.me/15551234567?text=', $url);

        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $this->assertStringContainsString('Hello ABC Restaurant,', $query['text']);
        $this->assertStringContainsString('Chicken Burger x2 - $10', $query['text']);
        $this->assertStringContainsString('Coke x1 - $2', $query['text']);
        $this->assertStringContainsString('Total: $12', $query['text']);
        $this->assertStringContainsString('Phone: +123456789', $query['text']);
    }

    public function test_inactive_product_cannot_be_ordered(): void
    {
        $store = $this->makeStore(User::factory()->create());
        $soup = $this->makeProduct($store, 'Seasonal Soup', 4, 'inactive');

        $this->postJson('/api/public/stores/abc-restaurant/orders', $this->payload([
            ['product_id' => $soup->id, 'quantity' => 1],
        ]))
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonValidationErrors(['items.0.product_id']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_product_from_another_store_cannot_be_ordered(): void
    {
        $owner = User::factory()->create();
        $store = $this->makeStore($owner);
        $this->makeProduct($store, 'Coke', 2);

        $otherStore = $this->makeStore(User::factory()->create(), 'Other Shop');
        $foreign = $this->makeProduct($otherStore, 'Foreign Item', 1);

        $this->postJson('/api/public/stores/abc-restaurant/orders', $this->payload([
            ['product_id' => $foreign->id, 'quantity' => 1],
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.product_id']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_invalid_request_returns_consistent_error_format(): void
    {
        $this->makeStore(User::factory()->create());

        $this->postJson('/api/public/stores/abc-restaurant/orders', [])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonValidationErrors(['customer_name', 'customer_phone', 'customer_address', 'items']);
    }

    public function test_inactive_store_is_not_public(): void
    {
        $this->makeStore(User::factory()->create(), 'Closed Shop', 'inactive');

        $this->getJson('/api/public/stores/closed-shop')
            ->assertNotFound()
            ->assertJsonPath('success', false);

        $this->get('/store/closed-shop')->assertNotFound();
    }

    public function test_public_store_hides_inactive_products(): void
    {
        $store = $this->makeStore(User::factory()->create());
        $this->makeProduct($store, 'Coke', 2);
        $this->makeProduct($store, 'Seasonal Soup', 4, 'inactive');

        $this->getJson('/api/public/stores/abc-restaurant/products')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Coke');

        $this->get('/store/abc-restaurant')
            ->assertOk()
            ->assertSee('Coke')
            ->assertDontSee('Seasonal Soup');
    }

    public function test_only_the_owner_can_view_or_update_an_order(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $store = $this->makeStore($owner);
        $coke = $this->makeProduct($store, 'Coke', 2);

        $order = app(OrderService::class)->createOrder($store, $this->payload([
            ['product_id' => $coke->id, 'quantity' => 1],
        ]));

        // A stranger is blocked.
        Sanctum::actingAs($stranger);
        $this->getJson("/api/orders/{$order->id}")->assertForbidden()->assertJsonPath('success', false);
        $this->putJson("/api/orders/{$order->id}/status", ['status' => 'confirmed'])->assertForbidden();
        $this->getJson("/api/stores/{$store->id}/orders")->assertForbidden();

        // The owner can update the status.
        Sanctum::actingAs($owner);
        $this->putJson("/api/orders/{$order->id}/status", ['status' => 'confirmed'])
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmed');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'confirmed']);

        // Invalid statuses are rejected.
        $this->putJson("/api/orders/{$order->id}/status", ['status' => 'shipped'])->assertStatus(422);
    }

    public function test_protected_endpoints_require_authentication(): void
    {
        $this->getJson('/api/stores')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    }
}