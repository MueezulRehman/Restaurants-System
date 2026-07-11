<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\OrderController;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Tests\TestCase;

class OrderStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_status_update_still_succeeds_when_broadcast_fails(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'status' => 'active',
            'plan' => 'basic',
        ]);

        $user = User::factory()->create([
            'name' => 'Test Admin',
            'phone' => '9999999999',
            'role' => 'admin',
            'restaurant_id' => $restaurant->id,
        ]);

        $order = Order::create([
            'restaurant_id' => $restaurant->id,
            'order_number' => 'TH-TEST-0001',
            'tracking_token' => 'tracking-token-1',
            'order_type' => 'online',
            'status' => 'pending',
            'customer_name' => 'Test Customer',
            'customer_phone' => '1234567890',
            'subtotal' => 10.00,
            'total' => 10.00,
            'payment_method' => 'cash',
        ]);

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

        $response = app(OrderController::class)->updateStatus(new Request(['status' => 'confirmed']), $order);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('confirmed', $order->fresh()->status);
    }
}
