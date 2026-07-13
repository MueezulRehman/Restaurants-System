<?php

namespace Tests\Feature;

use App\Services\ModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleDefaultsTest extends TestCase
{
    use RefreshDatabase;

    public function test_general_store_defaults_include_store_modules_and_allergies(): void
    {
        ModuleService::seedDefaultModules();
        ModuleService::seedDefaultBusinessTypes();

        $keys = ModuleService::getDefaultModuleKeysForBusinessType('General Store');

        $this->assertContains('inventory', $keys);
        $this->assertContains('categories', $keys);
        $this->assertContains('allergies', $keys);
        $this->assertContains('general_store', $keys);
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
}
