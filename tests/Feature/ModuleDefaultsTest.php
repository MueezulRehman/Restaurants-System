<?php

namespace Tests\Feature;

use App\Services\ModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleDefaultsTest extends TestCase
{
    use RefreshDatabase;

    public function test_general_store_defaults_include_store_modules_without_medical_allergies(): void
    {
        ModuleService::seedDefaultModules();
        ModuleService::seedDefaultBusinessTypes();

        $keys = ModuleService::getDefaultModuleKeysForBusinessType('General Store');

        $this->assertContains('inventory', $keys);
        $this->assertContains('categories', $keys);
        $this->assertContains('general_store', $keys);
        $this->assertNotContains('allergies', $keys);
    }

    public function test_other_custom_defaults_are_non_medical(): void
    {
        ModuleService::seedDefaultModules();
        ModuleService::seedDefaultBusinessTypes();

        $keys = ModuleService::getDefaultModuleKeysForBusinessType('Other / Custom');

        $this->assertContains('pos', $keys);
        $this->assertContains('inventory', $keys);
        $this->assertContains('variants', $keys);
        $this->assertNotContains('allergies', $keys);
        $this->assertNotContains('medical', $keys);
        $this->assertNotContains('pharmacy', $keys);
    }

    public function test_mobile_shop_defaults_include_device_workflows(): void
    {
        ModuleService::seedDefaultModules();
        ModuleService::seedDefaultBusinessTypes();

        $keys = ModuleService::getDefaultModuleKeysForBusinessType('Mobile Shop');

        foreach (['suppliers', 'purchasing', 'sales-returns', 'warranty', 'repairs', 'trade-ins', 'installments', 'brands', 'barcode-labels', 'loyalty', 'stock-transfers', 'profit-margins'] as $module) {
            $this->assertContains($module, $keys);
        }

        $this->assertNotContains('medical', $keys);
        $this->assertNotContains('allergies', $keys);
    }

    public function test_clothing_store_defaults_include_garment_workflows(): void
    {
        ModuleService::seedDefaultModules();
        ModuleService::seedDefaultBusinessTypes();

        $keys = ModuleService::getDefaultModuleKeysForBusinessType('Clothing Store');

        foreach (['suppliers', 'purchasing', 'sales-returns', 'brands', 'collections', 'barcode-labels', 'loyalty', 'stock-transfers', 'profit-margins'] as $module) {
            $this->assertContains($module, $keys);
        }

        $this->assertNotContains('warranty', $keys);
        $this->assertNotContains('medical', $keys);
        $this->assertNotContains('allergies', $keys);
    }

    public function test_pharmacy_defaults_include_pharmacy_modules_and_allergies(): void
    {
        ModuleService::seedDefaultModules();
        ModuleService::seedDefaultBusinessTypes();

        $keys = ModuleService::getDefaultModuleKeysForBusinessType('Pharmacy');

        $this->assertContains('medical', $keys);
        $this->assertContains('medical-records', $keys);
        $this->assertContains('allergies', $keys);
        $this->assertContains('pharmacy', $keys);
    }

    public function test_new_business_types_are_available_and_general_business_is_legacy(): void
    {
        ModuleService::ensureDefaults();

        foreach (
            [
                'Grocery / Supermarket',
                'Wholesale / Distributor',
                'Salon / Beauty',
                'Clinic / Doctor',
                'Gym / Fitness',
                'Services / Repair Business',
                'Electronics Store',
                'Online Store',
            ] as $name
        ) {
            $this->assertDatabaseHas('business_types', ['name' => $name, 'is_active' => true]);
        }

        $this->assertDatabaseHas('business_types', ['name' => 'General Store', 'is_active' => true]);
        $this->assertDatabaseHas('business_types', ['name' => 'General Business', 'is_active' => false]);
    }

    public function test_recommended_business_types_have_explicit_pos_modes(): void
    {
        foreach (['Grocery / Supermarket', 'Wholesale / Distributor', 'Salon / Beauty', 'Gym / Fitness', 'Services / Repair Business', 'Electronics Store', 'Online Store'] as $type) {
            $this->assertSame('retail', config('pos.business_type_modes.' . strtolower($type)));
        }

        $this->assertSame('medical', config('pos.business_type_modes.clinic / doctor'));
    }
}
