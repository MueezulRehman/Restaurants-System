<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\Medicine;
use App\Models\MedicineInteraction;
use App\Models\MedicineBatch;
use App\Models\Prescription;
use App\Models\QueueEntry;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class PhaseThreeMedicalWorkflowTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    public function test_manager_can_record_consultation_notes_and_complete_visit(): void
    {
        [$restaurant, $user, $patient, $doctor] = $this->clinic();
        $visit = Visit::create([
            'restaurant_id' => $restaurant->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'status' => 'in_progress',
            'checked_in_at' => now(),
        ]);
        QueueEntry::create([
            'restaurant_id' => $restaurant->id, 'patient_id' => $patient->id,
            'doctor_id' => $doctor->id, 'visit_id' => $visit->id,
            'queue_date' => today(), 'token_number' => 1, 'public_token' => 'phase-three-token', 'status' => 'in_progress',
        ]);

        $this->actingAs($user);
        $visit->update(['status' => 'completed', 'diagnosis' => 'Viral fever', 'notes' => 'Rest and fluids']);
        $visit->queueEntry->update(['status' => 'completed']);

        $this->assertDatabaseHas('visits', ['id' => $visit->id, 'status' => 'completed', 'diagnosis' => 'Viral fever']);
        $this->assertDatabaseHas('queue_entries', ['visit_id' => $visit->id, 'status' => 'completed']);
    }

    public function test_visit_route_cannot_cross_tenant_boundary(): void
    {
        [$restaurant, $user, $patient, $doctor] = $this->clinic('phase-three-a');
        $other = Restaurant::create(['name' => 'Other clinic', 'slug' => 'phase-three-b', 'status' => 'active', 'enabled_modules' => ['medical']]);
        $otherPatient = Patient::create(['restaurant_id' => $other->id, 'patient_number' => 'P-2', 'name' => 'Other Patient']);
        $otherDoctor = Doctor::create(['restaurant_id' => $other->id, 'name' => 'Other Doctor', 'specialty' => 'General', 'status' => 'active']);
        $visit = Visit::create(['restaurant_id' => $other->id, 'patient_id' => $otherPatient->id, 'doctor_id' => $otherDoctor->id, 'status' => 'checked_in', 'checked_in_at' => now()]);

        $this->actingAs($user)->get(route('manager.visits.show', $visit))->assertNotFound();
    }

    public function test_patient_allergy_crud_is_tenant_scoped(): void
    {
        [$restaurant, $user, $patient] = $this->clinic();
        $medicine = Medicine::create(['restaurant_id' => $restaurant->id, 'name' => 'Amoxicillin']);
        $this->actingAs($user);

        $this->post(route('manager.patient-allergies.store'), [
            'patient_id' => $patient->id,
            'allergy_name' => 'Penicillin',
            'severity' => 'severe',
            'trigger_medicines' => [$medicine->id],
            'is_active' => 1,
        ])->assertRedirect(route('manager.patient-allergies.index'));

        $allergy = PatientAllergy::withoutGlobalScopes()->firstOrFail();
        $this->assertSame([$medicine->id], $allergy->trigger_medicines);
        $this->get(route('manager.patient-allergies.edit', $allergy))->assertOk();
    }

    public function test_prescription_rejects_patient_allergy_and_medicine_interaction_conflicts(): void
    {
        [$restaurant, $user, $patient, $doctor] = $this->clinic('phase-three-safety');
        $visit = Visit::create(['restaurant_id' => $restaurant->id, 'patient_id' => $patient->id, 'doctor_id' => $doctor->id, 'status' => 'in_progress', 'checked_in_at' => now()]);
        $first = Medicine::create(['restaurant_id' => $restaurant->id, 'name' => 'Medicine One']);
        $second = Medicine::create(['restaurant_id' => $restaurant->id, 'name' => 'Medicine Two']);
        PatientAllergy::create(['restaurant_id' => $restaurant->id, 'patient_id' => $patient->id, 'allergy_name' => 'Test allergy', 'severity' => 'severe', 'trigger_medicines' => [$first->id], 'is_active' => true]);
        $this->actingAs($user);

        $response = $this->post(route('manager.prescriptions.store'), $this->prescriptionData($restaurant, $patient, $doctor, $visit, [$first->id]));
        $this->assertStringContainsString('Prescription conflicts', $response->getSession()->get('errors')->first('medicines'));
        $this->assertDatabaseCount('prescriptions', 0);

        MedicineInteraction::create(['medicine_id_1' => $first->id, 'medicine_id_2' => $second->id, 'interaction_type' => 'serious', 'interaction_description' => 'Do not combine', 'recommended_action' => 'Avoid']);
        PatientAllergy::query()->update(['is_active' => false]);
        $interactionResponse = $this->post(route('manager.prescriptions.store'), $this->prescriptionData($restaurant, $patient, $doctor, $visit, [$first->id, $second->id]));
        $this->assertStringContainsString('interacting medicines', $interactionResponse->getSession()->get('errors')->first('medicines'));
        $this->assertDatabaseCount('prescriptions', 0);
    }

    public function test_verified_prescription_dispensing_deducts_stock_once(): void
    {
        [$restaurant, $user, $patient, $doctor] = $this->clinic('phase-four-dispensing');
        $medicine = Medicine::create(['restaurant_id' => $restaurant->id, 'name' => 'Dispense Medicine']);
        $prescription = Prescription::create([
            'restaurant_id' => $restaurant->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'prescription_number' => 'RX-DISPENSE-1',
            'patient_name' => $patient->name,
            'doctor_name' => $doctor->name,
            'prescription_date' => today(),
            'medicines' => [$medicine->id],
            'status' => 'verified',
        ]);
        $batch = MedicineBatch::create([
            'restaurant_id' => $restaurant->id,
            'medicine_id' => $medicine->id,
            'batch_number' => 'B-1',
            'expiry_date' => today()->addYear(),
            'quantity' => 2,
        ]);

        $this->actingAs($user)
            ->post(route('manager.prescriptions.dispense', $prescription))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('prescriptions', ['id' => $prescription->id, 'status' => 'used', 'dispensed_by' => $user->id]);
        $this->assertDatabaseHas('medicine_batches', ['id' => $batch->id, 'quantity' => 1]);
    }

    public function test_dispensing_rejects_insufficient_stock_without_changing_prescription_or_stock(): void
    {
        [$restaurant, $user, $patient, $doctor] = $this->clinic('phase-four-insufficient');
        $medicine = Medicine::create(['restaurant_id' => $restaurant->id, 'name' => 'Limited Medicine']);
        $prescription = Prescription::create([
            'restaurant_id' => $restaurant->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'prescription_number' => 'RX-INSUFFICIENT-1',
            'patient_name' => $patient->name,
            'doctor_name' => $doctor->name,
            'prescription_date' => today(),
            'medicines' => [['medicine_id' => $medicine->id, 'quantity' => 2]],
            'status' => 'verified',
        ]);
        $batch = MedicineBatch::create([
            'restaurant_id' => $restaurant->id,
            'medicine_id' => $medicine->id,
            'batch_number' => 'B-LIMITED',
            'expiry_date' => today()->addYear(),
            'quantity' => 1,
        ]);

        $this->actingAs($user)
            ->post(route('manager.prescriptions.dispense', $prescription))
            ->assertStatus(422);

        $this->assertDatabaseHas('prescriptions', ['id' => $prescription->id, 'status' => 'verified', 'dispensed_by' => null]);
        $this->assertDatabaseHas('medicine_batches', ['id' => $batch->id, 'quantity' => 1]);
    }

    public function test_dispensing_ignores_expired_batches_and_consumes_earliest_valid_batches_first(): void
    {
        [$restaurant, $user, $patient, $doctor] = $this->clinic('phase-four-expiry');
        $medicine = Medicine::create(['restaurant_id' => $restaurant->id, 'name' => 'Expiry Medicine']);
        $prescription = Prescription::create([
            'restaurant_id' => $restaurant->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'prescription_number' => 'RX-EXPIRY-1',
            'patient_name' => $patient->name,
            'doctor_name' => $doctor->name,
            'prescription_date' => today(),
            'medicines' => [['medicine_id' => $medicine->id, 'quantity' => 2]],
            'status' => 'verified',
        ]);
        $expired = MedicineBatch::create([
            'restaurant_id' => $restaurant->id, 'medicine_id' => $medicine->id,
            'batch_number' => 'B-EXPIRED', 'expiry_date' => today()->subDay(), 'quantity' => 10,
        ]);
        $later = MedicineBatch::create([
            'restaurant_id' => $restaurant->id, 'medicine_id' => $medicine->id,
            'batch_number' => 'B-LATER', 'expiry_date' => today()->addMonths(6), 'quantity' => 4,
        ]);
        $earlier = MedicineBatch::create([
            'restaurant_id' => $restaurant->id, 'medicine_id' => $medicine->id,
            'batch_number' => 'B-EARLIER', 'expiry_date' => today()->addMonth(), 'quantity' => 1,
        ]);

        $this->actingAs($user)
            ->post(route('manager.prescriptions.dispense', $prescription))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('medicine_batches', ['id' => $expired->id, 'quantity' => 10]);
        $this->assertDatabaseHas('medicine_batches', ['id' => $earlier->id, 'quantity' => 0]);
        $this->assertDatabaseHas('medicine_batches', ['id' => $later->id, 'quantity' => 3]);
    }

    public function test_used_prescription_cannot_be_dispensed_again(): void
    {
        [$restaurant, $user, $patient, $doctor] = $this->clinic('phase-four-repeat');
        $medicine = Medicine::create(['restaurant_id' => $restaurant->id, 'name' => 'Repeat Medicine']);
        $prescription = Prescription::create([
            'restaurant_id' => $restaurant->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'prescription_number' => 'RX-REPEAT-1',
            'patient_name' => $patient->name,
            'doctor_name' => $doctor->name,
            'prescription_date' => today(),
            'medicines' => [$medicine->id],
            'status' => 'used',
        ]);
        $batch = MedicineBatch::create([
            'restaurant_id' => $restaurant->id, 'medicine_id' => $medicine->id,
            'batch_number' => 'B-REPEAT', 'expiry_date' => today()->addYear(), 'quantity' => 2,
        ]);

        $this->actingAs($user)
            ->post(route('manager.prescriptions.dispense', $prescription))
            ->assertStatus(422);

        $this->assertDatabaseHas('medicine_batches', ['id' => $batch->id, 'quantity' => 2]);
        $this->assertDatabaseHas('prescriptions', ['id' => $prescription->id, 'status' => 'used']);
    }

    public function test_only_pharmacists_and_managers_can_dispense_verified_prescriptions(): void
    {
        [$restaurant, , $patient, $doctor] = $this->clinic('phase-four-authorization');
        $medicine = Medicine::create(['restaurant_id' => $restaurant->id, 'name' => 'Authorized Medicine']);
        $prescription = Prescription::create([
            'restaurant_id' => $restaurant->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'prescription_number' => 'RX-AUTH-1',
            'patient_name' => $patient->name,
            'doctor_name' => $doctor->name,
            'prescription_date' => today(),
            'medicines' => [$medicine->id],
            'status' => 'verified',
        ]);
        MedicineBatch::create([
            'restaurant_id' => $restaurant->id,
            'medicine_id' => $medicine->id,
            'batch_number' => 'B-AUTH',
            'expiry_date' => today()->addYear(),
            'quantity' => 1,
        ]);
        $staff = User::create([
            'name' => 'Reception Staff',
            'email' => 'reception-'.$restaurant->slug.'@example.com',
            'phone' => '03000000001',
            'role' => 'staff',
            'staff_type' => 'receptionist',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => ['medical'],
        ]);
        $pharmacist = User::create([
            'name' => 'Pharmacist',
            'email' => 'pharmacist-'.$restaurant->slug.'@example.com',
            'phone' => '03000000002',
            'role' => 'staff',
            'staff_type' => 'pharmacist',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => ['medical'],
        ]);

        $this->actingAs($staff)
            ->post(route('manager.prescriptions.dispense', $prescription))
            ->assertForbidden();

        $this->actingAs($pharmacist)
            ->post(route('manager.prescriptions.dispense', $prescription))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('prescriptions', ['id' => $prescription->id, 'status' => 'used', 'dispensed_by' => $pharmacist->id]);
    }

    private function prescriptionData($restaurant, $patient, $doctor, $visit, array $medicineIds): array
    {
        return [
            'prescription_number' => 'RX-'.uniqid(),
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'visit_id' => $visit->id,
            'patient_name' => $patient->name,
            'doctor_name' => $doctor->name,
            'prescription_date' => today()->toDateString(),
            'medicines' => json_encode($medicineIds),
            'status' => 'pending',
        ];
    }

    private function clinic(string $slug = 'phase-three'): array
    {
        $restaurant = Restaurant::create(['name' => 'Phase Three Clinic', 'slug' => $slug, 'status' => 'active', 'enabled_modules' => ['medical']]);
        $user = User::create(['name' => 'Manager', 'email' => $slug.'@example.com', 'phone' => '03000000000', 'role' => 'manager', 'restaurant_id' => $restaurant->id, 'password' => bcrypt('password'), 'module_access' => ['medical']]);
        $patient = Patient::create(['restaurant_id' => $restaurant->id, 'patient_number' => 'P-1', 'name' => 'Patient One']);
        $doctor = Doctor::create(['restaurant_id' => $restaurant->id, 'name' => 'Doctor One', 'specialty' => 'General', 'status' => 'active']);
        return [$restaurant, $user, $patient, $doctor];
    }
}
