<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_page_renders_with_a_customer_variable(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test Bistro',
            'slug' => 'test-bistro',
            'status' => 'active',
            'enabled_modules' => ['orders', 'pos'],
        ]);

        $restaurant->subscription()->create([
            'status' => 'active',
            'auto_renew' => true,
            'trial_ends_at' => now()->addDays(10),
        ]);

        $response = $this->get('/checkout?restaurant_id=' . $restaurant->id);

        $response->assertStatus(200);
        $response->assertViewHas('customer', null);
        $response->assertSee('Checkout');
    }

    public function test_tracking_page_renders_without_a_shared_restaurant_binding(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Track Bistro',
            'slug' => 'track-bistro',
            'status' => 'active',
            'enabled_modules' => ['orders'],
        ]);

        $order = Order::create([
            'restaurant_id' => $restaurant->id,
            'order_type' => 'delivery',
            'status' => 'pending',
            'customer_name' => 'Test Customer',
            'customer_phone' => '1234567890',
            'address' => '123 Main St',
            'subtotal' => 100,
            'delivery_fee' => 10,
            'total' => 110,
            'payment_method' => 'cash',
            'tracking_token' => 'tracking-token-123',
        ]);

        $response = $this->get('/track/' . $order->tracking_token);

        $response->assertStatus(200);
        $response->assertSee($order->order_number);
    }

    public function test_tracking_page_shows_restaurant_logo_when_available(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Logo Bistro',
            'slug' => 'logo-bistro',
            'status' => 'active',
            'enabled_modules' => ['orders'],
            'logo_path' => 'restaurant-logos/logo-bistro.png',
        ]);

        $order = Order::create([
            'restaurant_id' => $restaurant->id,
            'order_type' => 'delivery',
            'status' => 'pending',
            'customer_name' => 'Test Customer',
            'customer_phone' => '1234567890',
            'address' => '123 Main St',
            'subtotal' => 100,
            'delivery_fee' => 10,
            'total' => 110,
            'payment_method' => 'cash',
            'tracking_token' => 'tracking-token-123',
        ]);

        $response = $this->get('/track/' . $order->tracking_token);

        $response->assertStatus(200);
        $response->assertSee('Logo Bistro');
        $response->assertSee('storage/restaurant-logos/logo-bistro.png');
    }
}
