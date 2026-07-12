<?php

namespace Tests\Feature;

use App\Models\BusinessType;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineCategory;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class AdvancedMedicalPosTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    public function test_medical_pos_renders_seeded_medicines_and_batches(): void
    {
        $businessType = BusinessType::create([
            'name' => 'Medical Store',
            'description' => 'Medical store',
            'is_active' => true,
        ]);

        $restaurant = Restaurant::create([
            'name' => 'Medi POS Test',
            'slug' => 'medi-pos-test',
            'status' => 'active',
            'business_type_id' => $businessType->id,
        ]);

        $user = User::create([
            'name' => 'Pharma Manager',
            'email' => 'pharma-manager@example.com',
            'phone' => '1112223334',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => ['pos', 'medical'],
        ]);

        $this->actingAs($user);

        $category = MedicineCategory::create([
            'name' => 'Analgesics',
            'status' => true,
        ]);

        $medicine = Medicine::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Panadol Extra',
            'generic_name' => 'Paracetamol',
            'category_id' => $category->id,
            'sku' => 'PA-100',
            'requires_prescription' => false,
            'track_stock' => true,
            'min_stock' => 10,
        ]);

        MedicineBatch::create([
            'restaurant_id' => null,
            'medicine_id' => $medicine->id,
            'batch_number' => 'BATCH-001',
            'purchase_price' => 80,
            'selling_price' => 120,
            'quantity' => 25,
            'expiry_date' => now()->addMonths(6)->toDateString(),
        ]);

        $response = $this->get(route('manager.pos.index'));

        $response->assertOk();
        $response->assertSee('Medical Store POS');
        $response->assertSee('Panadol Extra');
        $response->assertSee('BATCH-001');
        $response->assertSee('Analgesics');
    }
}
