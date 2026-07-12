<?php

namespace Tests\Feature;

use App\Models\BusinessType;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\ModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleAccessExpansionTest extends TestCase
{
    use RefreshDatabase;

    public function test_pharmacy_and_general_store_access_expand_to_related_modules(): void
    {
        ModuleService::ensureDefaults();

        $pharmacyType = BusinessType::where('name', 'Medical Store')->firstOrFail();
        $generalStoreType = BusinessType::where('name', 'General Business')->firstOrFail();

        $pharmacyRestaurant = Restaurant::create([
            'name' => 'Pharma Test',
            'slug' => 'pharma-test',
            'status' => 'active',
            'business_type_id' => $pharmacyType->id,
        ]);

        $generalStoreRestaurant = Restaurant::create([
            'name' => 'General Store Test',
            'slug' => 'general-store-test',
            'status' => 'active',
            'business_type_id' => $generalStoreType->id,
        ]);

        $pharmacyManager = User::create([
            'name' => 'Pharmacy Manager',
            'email' => 'pharmacy-manager@example.com',
            'phone' => '1112223335',
            'role' => 'manager',
            'restaurant_id' => $pharmacyRestaurant->id,
            'password' => bcrypt('password'),
            'module_access' => ['pharmacy'],
        ]);

        $generalStoreManager = User::create([
            'name' => 'General Store Manager',
            'email' => 'general-manager@example.com',
            'phone' => '1112223336',
            'role' => 'manager',
            'restaurant_id' => $generalStoreRestaurant->id,
            'password' => bcrypt('password'),
            'module_access' => ['general_store'],
        ]);

        $this->assertTrue($pharmacyManager->hasModuleAccess('medical'));
        $this->assertTrue($pharmacyManager->hasModuleAccess('inventory'));
        $this->assertTrue($pharmacyManager->hasModuleAccess('stock'));
        $this->assertTrue($pharmacyManager->hasModuleAccess('pos'));

        $this->assertTrue($generalStoreManager->hasModuleAccess('inventory'));
        $this->assertTrue($generalStoreManager->hasModuleAccess('stock'));
        $this->assertTrue($generalStoreManager->hasModuleAccess('pos'));
        $this->assertTrue($generalStoreManager->hasModuleAccess('categories'));
        $this->assertTrue($generalStoreManager->hasModuleAccess('variants'));
    }
}
