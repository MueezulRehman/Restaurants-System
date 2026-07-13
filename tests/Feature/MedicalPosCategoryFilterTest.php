<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineCategory;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalPosCategoryFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_medical_pos_displays_medicine_categories(): void
    {
        $pharmacy = Restaurant::create([
            'name' => 'Test Pharmacy',
            'slug' => 'test-pharmacy',
            'status' => 'active',
            'enabled_modules' => ['pos', 'medical', 'medical-records', 'allergies', 'pharmacy'],
        ]);

        $manager = User::create([
            'name' => 'Pharmacy Manager',
            'email' => 'pharma@test.com',
            'phone' => '03001234567',
            'password' => bcrypt('password'),
            'role' => 'manager',
            'restaurant_id' => $pharmacy->id,
            'module_access' => ['pos', 'medical', 'medical-records', 'allergies', 'pharmacy'],
        ]);

        $ointmentCategory = MedicineCategory::create(['name' => 'Ointments', 'sort_order' => 1]);
        $painCategory = MedicineCategory::create(['name' => 'Pain Relief', 'sort_order' => 2]);

        $ointment = Medicine::create([
            'name' => 'Healing Ointment',
            'generic_name' => 'Generic Ointment',
            'category_id' => $ointmentCategory->id,
            'sku' => 'OIN001',
            'track_stock' => true,
        ]);

        $painMedicine = Medicine::create([
            'name' => 'Pain Relief Tablet',
            'generic_name' => 'Ibuprofen',
            'category_id' => $painCategory->id,
            'sku' => 'PAR001',
            'track_stock' => true,
        ]);

        $ointmentBatch = MedicineBatch::create([
            'medicine_id' => $ointment->id,
            'batch_number' => 'OIN001-2024',
            'quantity' => 50,
            'selling_price' => 150,
            'purchase_price' => 100,
            'expiry_date' => now()->addMonths(12),
        ]);

        $painBatch = MedicineBatch::create([
            'medicine_id' => $painMedicine->id,
            'batch_number' => 'PAR001-2024',
            'quantity' => 100,
            'selling_price' => 50,
            'purchase_price' => 30,
            'expiry_date' => now()->addMonths(24),
        ]);

        $this->actingAs($manager);
        $response = $this->get(route('manager.pos.index'));

        $response->assertStatus(200);
        $response->assertViewHas('medicineCategories');
        $response->assertViewHas('showMedicalItems', true);

        $categories = $response->viewData('medicineCategories');
        $this->assertCount(2, $categories);
        $this->assertEquals('Ointments', $categories[0]->name);
        $this->assertEquals('Pain Relief', $categories[1]->name);
    }

    public function test_medical_pos_groups_medicines_by_category(): void
    {
        $pharmacy = Restaurant::create([
            'name' => 'Test Pharmacy',
            'slug' => 'test-pharmacy',
            'status' => 'active',
            'enabled_modules' => ['pos', 'medical', 'medical-records', 'allergies', 'pharmacy'],
        ]);

        $manager = User::create([
            'name' => 'Pharmacy Manager',
            'email' => 'pharma@test.com',
            'phone' => '03001234567',
            'password' => bcrypt('password'),
            'role' => 'manager',
            'restaurant_id' => $pharmacy->id,
            'module_access' => ['pos', 'medical', 'medical-records', 'allergies', 'pharmacy'],
        ]);

        $category = MedicineCategory::create(['name' => 'Antibiotics', 'sort_order' => 1]);

        $medicine1 = Medicine::create([
            'name' => 'Antibiotic A',
            'category_id' => $category->id,
            'sku' => 'AB001',
        ]);

        $medicine2 = Medicine::create([
            'name' => 'Antibiotic B',
            'category_id' => $category->id,
            'sku' => 'AB002',
        ]);

        MedicineBatch::create([
            'medicine_id' => $medicine1->id,
            'batch_number' => 'AB001-2024',
            'quantity' => 50,
            'selling_price' => 100,
            'purchase_price' => 60,
            'expiry_date' => now()->addMonths(6),
        ]);

        MedicineBatch::create([
            'medicine_id' => $medicine2->id,
            'batch_number' => 'AB002-2024',
            'quantity' => 75,
            'selling_price' => 120,
            'purchase_price' => 70,
            'expiry_date' => now()->addMonths(8),
        ]);

        $this->actingAs($manager);
        $response = $this->get(route('manager.pos.index'));

        $response->assertStatus(200);
        $categories = $response->viewData('medicineCategories');
        $this->assertCount(1, $categories);

        $antibiotics = $categories->first();
        $this->assertEquals('Antibiotics', $antibiotics->name);
        $this->assertCount(2, $antibiotics->medicines);
    }

    public function test_medical_pos_handles_uncategorized_medicines(): void
    {
        $pharmacy = Restaurant::create([
            'name' => 'Test Pharmacy',
            'slug' => 'test-pharmacy',
            'status' => 'active',
            'enabled_modules' => ['pos', 'medical', 'medical-records', 'allergies', 'pharmacy'],
        ]);

        $manager = User::create([
            'name' => 'Pharmacy Manager',
            'email' => 'pharma@test.com',
            'phone' => '03001234567',
            'password' => bcrypt('password'),
            'role' => 'manager',
            'restaurant_id' => $pharmacy->id,
            'module_access' => ['pos', 'medical', 'medical-records', 'allergies', 'pharmacy'],
        ]);

        $uncategorized = Medicine::create([
            'name' => 'Unknown Medicine',
            'category_id' => null,
            'sku' => 'UNK001',
        ]);

        MedicineBatch::create([
            'medicine_id' => $uncategorized->id,
            'batch_number' => 'UNK001-2024',
            'quantity' => 10,
            'selling_price' => 80,
            'purchase_price' => 50,
            'expiry_date' => now()->addMonths(6),
        ]);

        $this->actingAs($manager);
        $response = $this->get(route('manager.pos.index'));

        $response->assertStatus(200);
        $response->assertViewHas('uncategorized');

        $uncategorizedMedicines = $response->viewData('uncategorized');
        $this->assertCount(1, $uncategorizedMedicines);
        $this->assertNull($uncategorizedMedicines->first()->category_id);
    }
}
