<?php

namespace Tests\Feature;

use App\Models\MedicalRecord;
use App\Models\Restaurant;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class MedicalFlowsTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    public function test_purchase_stock_adjustments_can_use_null_variant_id_for_medicine_batches(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Medi Test',
            'slug' => 'medi-test',
            'status' => 'active',
            'enabled_modules' => ['medical-records', 'orders'],
        ]);

        $user = User::create([
            'name' => 'Manager',
            'email' => 'manager2@example.com',
            'phone' => '1234567890',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => ['medical-records', 'orders'],
        ]);

        $this->actingAs($user);

        $adjustment = StockAdjustment::create([
            'restaurant_id' => $restaurant->id,
            'product_variant_id' => null,
            'user_id' => $user->id,
            'quantity_before' => 0,
            'quantity_after' => 20,
            'change_quantity' => 20,
            'reason' => 'purchase',
            'notes' => 'Purchase received',
        ]);

        $this->assertDatabaseHas('stock_adjustments', [
            'id' => $adjustment->id,
            'product_variant_id' => null,
        ]);
    }

    public function test_medical_record_can_be_saved_and_displayed(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Medi Record Test',
            'slug' => 'medi-record-test',
            'status' => 'active',
            'enabled_modules' => ['medical-records'],
        ]);

        $user = User::create([
            'name' => 'Doctor',
            'email' => 'doctor@example.com',
            'phone' => '1234567891',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => ['medical-records'],
        ]);

        $this->actingAs($user);

        $response = $this->post(route('manager.medical-records.store'), [
            'patient_name' => 'Ali Khan',
            'medicine_name' => 'Paracetamol',
            'notes' => 'Fever treatment',
        ]);

        $response->assertRedirect(route('manager.medical-records.index'));
        $this->assertDatabaseHas('medical_records', [
            'patient_name' => 'Ali Khan',
            'medicine_name' => 'Paracetamol',
            'notes' => 'Fever treatment',
        ]);
    }
}
