<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\ModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_seed_creates_fast_food_and_medical_business_types(): void
    {
        ModuleService::seedDefaultModules();
        ModuleService::seedDefaultBusinessTypes();

        $this->assertDatabaseHas('modules', ['key' => 'medical-records']);
        $this->assertDatabaseHas('business_types', ['name' => 'Fast Food']);
        $this->assertDatabaseHas('business_types', ['name' => 'Medical Store']);
    }

    public function test_manager_access_is_restricted_until_granted_for_a_module(): void
    {
        ModuleService::seedDefaultModules();

        $restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'status' => 'active',
            'enabled_modules' => ['orders'],
        ]);

        $manager = User::create([
            'name' => 'Manager',
            'email' => 'manager@example.com',
            'phone' => '1234567890',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => [],
        ]);

        $this->assertFalse($manager->hasModuleAccess('orders'));

        $manager->forceFill(['module_access' => ['orders']])->save();

        $this->assertTrue($manager->hasModuleAccess('orders'));
    }

    public function test_delivery_and_stock_workflows_are_persisted_for_a_restaurant(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Delivery Test Restaurant',
            'slug' => 'delivery-test-restaurant',
            'status' => 'active',
            'enabled_modules' => ['delivery', 'stock'],
        ]);

        $order = Order::create([
            'restaurant_id' => $restaurant->id,
            'order_type' => 'delivery',
            'status' => 'ready',
            'customer_name' => 'Test Customer',
            'customer_phone' => '1000000000',
            'address' => '123 Main St',
            'subtotal' => 100,
            'delivery_fee' => 10,
            'total' => 110,
            'payment_method' => 'cash',
        ]);

        $delivery = Delivery::create([
            'order_id' => $order->id,
            'status' => 'assigned',
            'delivery_notes' => 'Leave at the main gate',
        ]);

        $delivery->update(['status' => 'on_the_way']);

        $this->assertSame('on_the_way', $delivery->fresh()->status);
        $this->assertSame($order->id, $delivery->order_id);
        $this->assertDatabaseHas('deliveries', ['order_id' => $order->id, 'status' => 'on_the_way']);
    }
}
