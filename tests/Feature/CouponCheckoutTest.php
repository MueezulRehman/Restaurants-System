<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_applies_percentage_coupon_and_tracks_usage(): void
    {
        [$restaurant, $item] = $this->makeStore();
        $coupon = Coupon::create(['restaurant_id' => $restaurant->id, 'code' => 'SAVE10', 'type' => 'percent', 'value' => 10, 'minimum_order' => 100, 'usage_limit' => 1, 'is_active' => true]);
        $user = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000017']);
        $this->actingAs($user, 'web');

        $response = $this->post('/checkout?restaurant_id=' . $restaurant->id, [
            'order_type' => 'takeaway',
            'customer_name' => 'Buyer',
            'customer_phone' => '03000000018',
            'payment_method' => 'cash',
            'coupon_code' => 'save10',
            'cart' => [['type' => 'menu_item', 'id' => $item->id, 'quantity' => 1]],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', ['restaurant_id' => $restaurant->id, 'discount_amount' => 30, 'coupon_code' => 'SAVE10', 'subtotal' => 270, 'total' => 270]);
        $this->assertSame(1, $coupon->fresh()->usage_count);
    }

    public function test_checkout_rejects_coupon_below_minimum_order(): void
    {
        [$restaurant, $item] = $this->makeStore();
        Coupon::create(['restaurant_id' => $restaurant->id, 'code' => 'BIGSAVE', 'type' => 'fixed', 'value' => 50, 'minimum_order' => 500, 'is_active' => true]);
        $user = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000019']);
        $this->actingAs($user, 'web');

        $response = $this->post('/checkout?restaurant_id=' . $restaurant->id, [
            'order_type' => 'takeaway',
            'customer_name' => 'Buyer',
            'customer_phone' => '03000000020',
            'payment_method' => 'cash',
            'coupon_code' => 'BIGSAVE',
            'cart' => [['type' => 'menu_item', 'id' => $item->id, 'quantity' => 1]],
        ]);

        $response->assertSessionHasErrors('checkout');
        $this->assertDatabaseMissing('orders', ['restaurant_id' => $restaurant->id]);
    }

    public function test_checkout_redirects_even_when_broadcasting_fails(): void
    {
        [$restaurant, $item] = $this->makeStore();
        $user = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000021']);
        $this->actingAs($user, 'web');

        config([
            'broadcasting.default' => 'pusher',
            'broadcasting.connections.pusher.driver' => 'pusher',
            'broadcasting.connections.pusher.key' => 'test',
            'broadcasting.connections.pusher.secret' => 'test',
            'broadcasting.connections.pusher.app_id' => '1',
            'broadcasting.connections.pusher.options.host' => '127.0.0.1',
            'broadcasting.connections.pusher.options.port' => 1,
            'broadcasting.connections.pusher.options.scheme' => 'http',
            'broadcasting.connections.pusher.options.encrypted' => false,
            'broadcasting.connections.pusher.options.useTLS' => false,
        ]);

        $response = $this->post('/checkout?restaurant_id=' . $restaurant->id, [
            'order_type' => 'takeaway',
            'customer_name' => 'Buyer',
            'customer_phone' => '03000000022',
            'payment_method' => 'cash',
            'cart' => [['type' => 'menu_item', 'id' => $item->id, 'quantity' => 1]],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', ['restaurant_id' => $restaurant->id]);
    }

    private function makeStore(): array
    {
        $restaurant = Restaurant::create(['name' => 'Coupon Store', 'slug' => 'coupon-store-' . uniqid(), 'status' => 'active', 'plan' => 'basic', 'enabled_modules' => ['orders', 'coupons']]);
        $category = Category::create(['restaurant_id' => $restaurant->id, 'name' => 'Goods', 'slug' => 'coupon-goods-' . uniqid(), 'is_active' => true]);
        $item = MenuItem::create(['restaurant_id' => $restaurant->id, 'category_id' => $category->id, 'name' => 'Product', 'price' => 300, 'is_available' => true, 'track_stock' => false]);
        return [$restaurant, $item];
    }
}
