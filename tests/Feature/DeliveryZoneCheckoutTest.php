<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DeliveryZone;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryZoneCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_checkout_uses_zone_fee(): void
    {
        [$restaurant, $item] = $this->makeStore();
        $zone = DeliveryZone::create(['restaurant_id' => $restaurant->id, 'name' => 'Central', 'area_pattern' => 'Central', 'fee' => 80, 'minimum_order' => 300, 'is_active' => true]);
        $user = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000013']);
        $this->actingAs($user, 'web');

        $response = $this->post('/checkout?restaurant_id=' . $restaurant->id, [
            'order_type' => 'delivery',
            'customer_name' => 'Buyer',
            'customer_phone' => '03000000014',
            'address' => 'Central Market',
            'delivery_zone_id' => $zone->id,
            'payment_method' => 'cash',
            'cart' => [['type' => 'menu_item', 'id' => $item->id, 'quantity' => 2]],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', ['restaurant_id' => $restaurant->id, 'delivery_fee' => 80, 'total' => 380]);
    }

    public function test_delivery_checkout_rejects_zone_below_minimum_order(): void
    {
        [$restaurant, $item] = $this->makeStore();
        $zone = DeliveryZone::create(['restaurant_id' => $restaurant->id, 'name' => 'North', 'area_pattern' => 'North', 'fee' => 50, 'minimum_order' => 500, 'is_active' => true]);
        $user = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000015']);
        $this->actingAs($user, 'web');

        $response = $this->post('/checkout?restaurant_id=' . $restaurant->id, [
            'order_type' => 'delivery',
            'customer_name' => 'Buyer',
            'customer_phone' => '03000000016',
            'address' => 'North Market',
            'delivery_zone_id' => $zone->id,
            'payment_method' => 'cash',
            'cart' => [['type' => 'menu_item', 'id' => $item->id, 'quantity' => 1]],
        ]);

        $response->assertSessionHasErrors('checkout');
        $this->assertDatabaseMissing('orders', ['restaurant_id' => $restaurant->id]);
    }

    private function makeStore(): array
    {
        $restaurant = Restaurant::create(['name' => 'Zone Store', 'slug' => 'zone-store-' . uniqid(), 'status' => 'active', 'plan' => 'basic', 'enabled_modules' => ['orders', 'delivery']]);
        $category = Category::create(['restaurant_id' => $restaurant->id, 'name' => 'Goods', 'slug' => 'goods-' . uniqid(), 'is_active' => true]);
        $item = MenuItem::create(['restaurant_id' => $restaurant->id, 'category_id' => $category->id, 'name' => 'Product', 'price' => 300, 'is_available' => true, 'track_stock' => false]);
        return [$restaurant, $item];
    }
}
