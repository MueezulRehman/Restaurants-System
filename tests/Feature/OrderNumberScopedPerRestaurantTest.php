<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderNumberScopedPerRestaurantTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_numbers_are_scoped_per_restaurant(): void
    {
        $restaurantA = Restaurant::create([
            'name' => 'Restaurant A',
            'slug' => 'restaurant-a',
            'status' => 'active',
        ]);

        $restaurantB = Restaurant::create([
            'name' => 'Restaurant B',
            'slug' => 'restaurant-b',
            'status' => 'active',
        ]);

        $orderA1 = Order::create([
            'restaurant_id' => $restaurantA->id,
            'customer_name' => 'Customer A1',
            'customer_phone' => '03001111111',
            'order_type' => 'takeaway',
            'channel' => 'pos',
            'status' => 'pending',
            'payment_method' => 'cash',
            'subtotal' => 100,
            'total' => 100,
        ]);

        $orderA2 = Order::create([
            'restaurant_id' => $restaurantA->id,
            'customer_name' => 'Customer A2',
            'customer_phone' => '03002222222',
            'order_type' => 'takeaway',
            'channel' => 'pos',
            'status' => 'pending',
            'payment_method' => 'cash',
            'subtotal' => 100,
            'total' => 100,
        ]);

        $orderB1 = Order::create([
            'restaurant_id' => $restaurantB->id,
            'customer_name' => 'Customer B1',
            'customer_phone' => '03003333333',
            'order_type' => 'takeaway',
            'channel' => 'pos',
            'status' => 'pending',
            'payment_method' => 'cash',
            'subtotal' => 100,
            'total' => 100,
        ]);

        $today = now()->format('Ymd');
        $this->assertStringContainsString("TH-{$today}-0001", $orderA1->order_number);
        $this->assertStringContainsString("TH-{$today}-0002", $orderA2->order_number);
        $this->assertStringContainsString("TH-{$today}-0001", $orderB1->order_number);
        $this->assertEquals($orderA1->order_number, $orderB1->order_number);
    }
}
