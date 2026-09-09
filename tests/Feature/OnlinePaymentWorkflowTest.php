<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnlinePaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_update_an_online_order_payment_state(): void
    {
        $restaurant = Restaurant::create(['name' => 'Online Store', 'slug' => 'online-store', 'status' => 'active', 'enabled_modules' => ['orders']]);
        $restaurant->subscription()->create(['status' => 'active', 'billing_cycle' => 'monthly']);
        $manager = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000034']);
        $order = Order::create(['restaurant_id' => $restaurant->id, 'order_type' => 'online', 'status' => 'pending', 'customer_name' => 'Online Buyer', 'customer_phone' => '03000000035', 'subtotal' => 100, 'total' => 100, 'payment_method' => 'online', 'payment_status' => 'pending']);

        $this->actingAs($manager, 'web')->patch(route('manager.orders.payment', $order), ['payment_status' => 'paid', 'payment_reference' => 'PAY-123'])->assertRedirect();
        $order = $order->fresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('PAY-123', $order->payment_reference);

        $this->patch(route('manager.orders.payment', $order), ['payment_status' => 'refunded'])->assertRedirect();
        $this->assertSame('refunded', $order->fresh()->payment_status);
    }
}
