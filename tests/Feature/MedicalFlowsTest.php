<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
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

    public function test_clinic_record_can_link_customer_and_appointment_with_follow_up(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Clinic Record Test',
            'slug' => 'clinic-record-test',
            'status' => 'active',
            'enabled_modules' => ['medical-records', 'appointments'],
        ]);
        $user = User::create([
            'name' => 'Clinic Doctor',
            'email' => 'clinic-doctor@example.com',
            'phone' => '1234567892',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => ['medical-records', 'appointments'],
        ]);
        $customer = Customer::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Patient One',
            'phone' => '1234567893',
            'password' => bcrypt('password'),
        ]);
        $appointment = Appointment::create([
            'restaurant_id' => $restaurant->id,
            'customer_id' => $customer->id,
            'service_name' => 'Consultation',
            'starts_at' => '2026-10-01 10:00',
            'ends_at' => '2026-10-01 10:30',
            'status' => 'confirmed',
        ]);

        $this->actingAs($user);
        $response = $this->post(route('manager.medical-records.store'), [
            'customer_id' => $customer->id,
            'appointment_id' => $appointment->id,
            'patient_name' => 'Patient One',
            'medicine_name' => 'None',
            'doctor_name' => 'Clinic Doctor',
            'diagnosis' => 'Seasonal allergy',
            'follow_up_at' => '2026-10-15 10:00',
        ]);

        $response->assertRedirect(route('manager.medical-records.index'));
        $this->assertDatabaseHas('medical_records', [
            'customer_id' => $customer->id,
            'appointment_id' => $appointment->id,
            'doctor_name' => 'Clinic Doctor',
            'diagnosis' => 'Seasonal allergy',
        ]);
    }
}
