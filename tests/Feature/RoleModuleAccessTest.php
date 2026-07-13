<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleModuleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_cannot_access_modules_outside_their_grant(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'status' => 'active',
            'enabled_modules' => ['pos', 'menu', 'categories', 'staff'],
        ]);

        $manager = User::create([
            'name' => 'Manager',
            'email' => 'manager@test.com',
            'phone' => '03001234567',
            'password' => bcrypt('password'),
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'module_access' => ['menu'],
        ]);

        $this->assertTrue($manager->hasModuleAccess('menu'));
        $this->assertFalse($manager->hasModuleAccess('staff'));
        $this->assertFalse($manager->hasModuleAccess('pos'));
    }

    public function test_admin_can_access_all_enabled_restaurant_modules(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'status' => 'active',
            'enabled_modules' => ['pos', 'menu', 'categories', 'staff', 'medical'],
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'phone' => '03001234568',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'restaurant_id' => $restaurant->id,
        ]);

        $this->assertTrue($admin->hasModuleAccess('menu'));
        $this->assertTrue($admin->hasModuleAccess('staff'));
        $this->assertTrue($admin->hasModuleAccess('pos'));
        $this->assertTrue($admin->hasModuleAccess('medical'));
    }

    public function test_staff_without_module_access_json_cannot_access_modules(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'status' => 'active',
            'enabled_modules' => ['pos', 'menu', 'staff'],
        ]);

        $staff = User::create([
            'name' => 'Staff',
            'email' => 'staff@test.com',
            'phone' => '03001234569',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'restaurant_id' => $restaurant->id,
            'module_access' => null,
        ]);

        $this->assertFalse($staff->hasModuleAccess('menu'));
        $this->assertFalse($staff->hasModuleAccess('staff'));
        $this->assertFalse($staff->hasModuleAccess('pos'));
    }

    public function test_pharmacy_manager_cannot_access_restaurant_only_modules(): void
    {
        $pharmacy = Restaurant::create([
            'name' => 'Test Pharmacy',
            'slug' => 'test-pharmacy',
            'status' => 'active',
            'enabled_modules' => ['pos', 'medical', 'medical-records', 'allergies', 'pharmacy'],
        ]);

        $manager = User::create([
            'name' => 'Pharmacy Manager',
            'email' => 'pharma-manager@test.com',
            'phone' => '03001234570',
            'password' => bcrypt('password'),
            'role' => 'manager',
            'restaurant_id' => $pharmacy->id,
            'module_access' => ['pharmacy'],
        ]);

        $this->assertTrue($manager->hasModuleAccess('medical'));
        $this->assertTrue($manager->hasModuleAccess('medical-records'));
        $this->assertTrue($manager->hasModuleAccess('allergies'));
        $this->assertFalse($manager->hasModuleAccess('tables'));
        $this->assertFalse($manager->hasModuleAccess('orders'));
    }

    public function test_store_manager_cannot_access_pharmacy_only_modules(): void
    {
        $store = Restaurant::create([
            'name' => 'Test Store',
            'slug' => 'test-store',
            'status' => 'active',
            'enabled_modules' => ['pos', 'inventory', 'allergies', 'general_store'],
        ]);

        $manager = User::create([
            'name' => 'Store Manager',
            'email' => 'store-manager@test.com',
            'phone' => '03001234571',
            'password' => bcrypt('password'),
            'role' => 'manager',
            'restaurant_id' => $store->id,
            'module_access' => ['general_store'],
        ]);

        $this->assertTrue($manager->hasModuleAccess('allergies'));
        $this->assertTrue($manager->hasModuleAccess('inventory'));
        $this->assertFalse($manager->hasModuleAccess('medical'));
        $this->assertFalse($manager->hasModuleAccess('medical-records'));
    }
}
