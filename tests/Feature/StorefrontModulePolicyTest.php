<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Services\ModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontModulePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        ModuleService::seedDefaultModules();
    }

    public function test_disabled_storefront_hides_customer_menu_modules(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Private Shop',
            'slug' => 'private-shop',
            'status' => 'active',
            'storefront_enabled' => false,
            'storefront_module_override' => false,
            'enabled_modules' => ['menu', 'categories', 'variants', 'pos', 'inventory'],
        ]);

        $this->assertFalse($restaurant->isModuleEnabled('menu'));
        $this->assertFalse($restaurant->isModuleEnabled('categories'));
        $this->assertTrue($restaurant->isModuleEnabled('pos'));
        $this->assertNotContains('menu', $restaurant->getEnabledModules()->pluck('key')->all());
    }

    public function test_super_admin_override_keeps_selected_menu_modules_available(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Internal Catalog',
            'slug' => 'internal-catalog',
            'status' => 'active',
            'storefront_enabled' => false,
            'storefront_module_override' => true,
            'enabled_modules' => ['menu', 'categories', 'pos'],
        ]);

        $this->assertTrue($restaurant->isModuleEnabled('menu'));
        $this->assertContains('categories', $restaurant->getEnabledModules()->pluck('key')->all());
    }
}
