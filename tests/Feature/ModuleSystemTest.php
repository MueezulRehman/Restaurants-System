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

    public function test_manager_without_explicit_grants_inherits_enabled_modules(): void
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

        $this->assertTrue($manager->hasModuleAccess('orders'));

        $manager->forceFill(['module_access' => ['orders']])->save();

        $this->assertTrue($manager->hasModuleAccess('orders'));
    }

    public function test_manager_sees_modules_enabled_for_business_by_super_admin(): void
    {
        ModuleService::seedDefaultModules();

        $restaurant = Restaurant::create([
            'name' => 'Super Admin Module Restaurant',
            'slug' => 'super-admin-module-restaurant',
            'status' => 'active',
            'enabled_modules' => ['orders'],
        ]);
        $manager = User::create([
            'name' => 'Business Manager',
            'email' => 'business-manager@example.com',
            'phone' => '1234567891',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => null,
        ]);

        // Super Admin updates the central business module selection.
        $restaurant->update(['enabled_modules' => ['orders', 'stock']]);
        $manager->refresh();

        $this->assertTrue($manager->hasModuleAccess('orders'));
        $this->assertTrue($manager->hasModuleAccess('stock'));

        $restaurant->update(['enabled_modules' => ['orders']]);
        $this->assertFalse($manager->fresh()->hasModuleAccess('stock'));
    }

    public function test_direct_manager_access_matches_super_admin_impersonation_for_same_business(): void
    {
        ModuleService::seedDefaultModules();

        $restaurant = Restaurant::create([
            'name' => 'Shared Access Restaurant',
            'slug' => 'shared-access-restaurant',
            'status' => 'active',
            'enabled_modules' => ['orders', 'pos', 'cashbook', 'feedback'],
        ]);
        $manager = User::create([
            'name' => 'Direct Manager',
            'email' => 'direct-manager@example.com',
            'phone' => '1234567892',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => ['menu'],
        ]);
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'shared-access-admin@example.com',
            'phone' => '1234567893',
            'role' => 'super_admin',
            'password' => bcrypt('password'),
        ]);

        $keys = ['orders', 'pos', 'cashbook', 'feedback', 'menu'];
        $directAccess = array_map(fn (string $key): bool => $manager->hasModuleAccess($key), $keys);

        \App\Support\Tenancy::enter($restaurant);
        $impersonatedAccess = array_map(fn (string $key): bool => $superAdmin->hasModuleAccess($key), $keys);
        \App\Support\Tenancy::exit();

        $this->assertSame($impersonatedAccess, $directAccess);
        $this->assertSame([true, true, true, true, false], $directAccess);

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
