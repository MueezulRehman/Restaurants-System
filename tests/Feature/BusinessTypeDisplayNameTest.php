<?php

namespace Tests\Feature;

use App\Models\BusinessType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessTypeDisplayNameTest extends TestCase
{
    use RefreshDatabase;

    public function test_general_business_and_medical_store_use_clearer_display_names(): void
    {
        $generalBusiness = BusinessType::create(['name' => 'General Business']);
        $medicalStore = BusinessType::create(['name' => 'Medical Store']);

        $this->assertSame('General Store', $generalBusiness->display_name);
        $this->assertSame('Pharmacy', $medicalStore->display_name);
    }
}
