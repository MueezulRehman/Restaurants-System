<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Patient;
use App\Models\QueueEntry;
use App\Models\Restaurant;
use App\Services\QueueNotificationService;
use App\Models\User;
use App\Models\Visit;
use App\Jobs\SendQueueNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class DoctorPatientManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_update_and_delete_doctor_and_patient(): void
    {
        [$restaurant, $manager] = $this->managerFor('clinic-a');
        $this->actingAs($manager);

        $this->post(route('manager.doctors.store'), [
            'name' => 'Dr. Sana',
            'specialty' => 'Cardiology',
            'phone' => '03000000001',
            'email' => 'sana@example.com',
            'address' => 'Clinic A',
            'status' => 'pending',
        ])->assertRedirect(route('manager.doctors.index'));

        $doctor = Doctor::withoutGlobalScopes()->where('restaurant_id', $restaurant->id)->firstOrFail();
        $this->assertSame('pending', $doctor->status);

        $this->patch(route('manager.doctors.update', $doctor), [
            'name' => 'Dr. Sana Khan',
            'specialty' => 'Cardiology',
            'phone' => '03000000001',
            'email' => 'sana@example.com',
            'address' => 'Clinic A',
            'status' => 'declined',
        ])->assertRedirect(route('manager.doctors.index'));

        $this->assertDatabaseHas('doctors', ['id' => $doctor->id, 'status' => 'declined']);

        $this->post(route('manager.patients.store'), [
            'patient_number' => 'PAT-001',
            'name' => 'Ali Khan',
            'phone' => '03000000002',
            'email' => 'ali@example.com',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'address' => 'Clinic A',
            'emergency_contact_name' => 'Sara Khan',
            'emergency_contact_phone' => '03000000003',
        ])->assertRedirect(route('manager.patients.index'));

        $patient = Patient::withoutGlobalScopes()->where('restaurant_id', $restaurant->id)->firstOrFail();
        $this->assertDatabaseHas('patients', ['id' => $patient->id, 'patient_number' => 'PAT-001']);

        $this->delete(route('manager.doctors.destroy', $doctor))->assertRedirect(route('manager.doctors.index'));
        $this->delete(route('manager.patients.destroy', $patient))->assertRedirect(route('manager.patients.index'));
        $this->assertDatabaseMissing('doctors', ['id' => $doctor->id]);
        $this->assertDatabaseMissing('patients', ['id' => $patient->id]);
    }

    public function test_patient_duplicate_cnic_is_rejected_and_existing_record_is_opened(): void
    {
        [$restaurant, $manager] = $this->managerFor('clinic-duplicates');
        $existing = Patient::withoutGlobalScopes()->create([
            'restaurant_id' => $restaurant->id,
            'patient_number' => 'PAT-001',
            'name' => 'Ali Khan',
            'cnic' => '35202-1234567-1',
        ]);

        $this->actingAs($manager);
        $response = $this->from(route('manager.patients.create'))->post(route('manager.patients.store'), [
            'patient_number' => 'PAT-002',
            'name' => 'Different Name',
            'cnic' => '35202-1234567-1',
        ]);

        $response->assertRedirect(route('manager.patients.edit', $existing));
        $response->assertSessionHasErrors('cnic');
        $this->assertDatabaseCount('patients', 1);
    }

    public function test_patient_without_cnic_uses_name_phone_and_date_of_birth_for_duplicates(): void
    {
        [$restaurant, $manager] = $this->managerFor('clinic-composite-duplicates');
        Patient::withoutGlobalScopes()->create([
            'restaurant_id' => $restaurant->id,
            'patient_number' => 'PAT-001',
            'name' => 'Ali Khan',
            'phone' => '03000000004',
            'date_of_birth' => '1990-01-01',
        ]);

        $this->actingAs($manager);
        $this->post(route('manager.patients.store'), [
            'patient_number' => 'PAT-002',
            'name' => 'Ali Khan',
            'phone' => '03000000004',
            'date_of_birth' => '1990-01-01',
        ])->assertRedirect(route('manager.patients.edit', Patient::withoutGlobalScopes()->where('patient_number', 'PAT-001')->first()));

        $this->assertDatabaseCount('patients', 1);
    }

    public function test_shared_guardian_phone_does_not_merge_dependent_and_guardian_patients(): void
    {
        [$restaurant, $manager] = $this->managerFor('clinic-dependents');
        Patient::withoutGlobalScopes()->create([
            'restaurant_id' => $restaurant->id,
            'patient_number' => 'PAT-GUARDIAN',
            'name' => 'Guardian Patient',
            'phone' => '03000000004',
            'date_of_birth' => '1970-01-01',
        ]);

        $this->actingAs($manager);
        $this->post(route('manager.patients.store'), [
            'patient_number' => 'PAT-DEPENDENT',
            'name' => 'Dependent Patient',
            'phone' => '03000000004',
            'date_of_birth' => '2018-01-01',
            'is_dependent' => 1,
            'guardian_name' => 'Guardian Patient',
            'guardian_cnic' => '35202-7654321-1',
            'guardian_phone' => '03000000004',
            'relationship' => 'guardian',
        ])->assertRedirect(route('manager.patients.index'));

        $this->assertDatabaseCount('patients', 2);
    }

    public function test_doctors_and_patients_are_isolated_between_restaurants(): void
    {
        [$restaurantA, $managerA] = $this->managerFor('clinic-isolation-a');
        [$restaurantB] = $this->managerFor('clinic-isolation-b');
        $doctorB = Doctor::withoutGlobalScopes()->create([
            'restaurant_id' => $restaurantB->id,
            'name' => 'Dr. Other',
            'specialty' => 'Dermatology',
            'status' => 'active',
        ]);
        $patientB = Patient::withoutGlobalScopes()->create([
            'restaurant_id' => $restaurantB->id,
            'patient_number' => 'PAT-B',
            'name' => 'Other Patient',
            'phone' => '03000000006',
        ]);

        $this->actingAs($managerA);
        $this->get(route('manager.doctors.edit', $doctorB))->assertNotFound();
        $this->get(route('manager.patients.edit', $patientB))->assertNotFound();
        $this->delete(route('manager.doctors.destroy', $doctorB))->assertNotFound();
        $this->delete(route('manager.patients.destroy', $patientB))->assertNotFound();
    }

    public function test_medical_pages_require_the_business_medical_module(): void
    {
        [$restaurant, $manager] = $this->managerFor('clinic-module-gate');
        $this->actingAs($manager);

        $restaurant->update(['enabled_modules' => []]);
        $this->get(route('manager.doctors.index'))->assertForbidden();

        $restaurant->update(['enabled_modules' => ['medical']]);
        $this->get(route('manager.doctors.index'))->assertOk();
        $this->get(route('manager.patients.index'))->assertOk();
    }

    public function test_reception_issues_independent_daily_tokens_and_queue_advances_manually(): void
    {
        [$restaurant, $manager] = $this->managerFor('clinic-queue');
        $doctorA = Doctor::withoutGlobalScopes()->create(['restaurant_id' => $restaurant->id, 'name' => 'Dr. A', 'specialty' => 'General', 'status' => 'active']);
        $doctorB = Doctor::withoutGlobalScopes()->create(['restaurant_id' => $restaurant->id, 'name' => 'Dr. B', 'specialty' => 'General', 'status' => 'active']);
        $patientA = Patient::withoutGlobalScopes()->create(['restaurant_id' => $restaurant->id, 'patient_number' => 'PAT-QA', 'name' => 'Patient A']);
        $patientB = Patient::withoutGlobalScopes()->create(['restaurant_id' => $restaurant->id, 'patient_number' => 'PAT-QB', 'name' => 'Patient B']);
        $this->actingAs($manager);

        $this->post(route('manager.medical-queue.store'), ['doctor_id' => $doctorA->id, 'patient_id' => $patientA->id])->assertRedirect();
        $this->post(route('manager.medical-queue.store'), ['doctor_id' => $doctorA->id, 'patient_id' => $patientB->id])->assertRedirect();
        $this->post(route('manager.medical-queue.store'), ['doctor_id' => $doctorB->id, 'patient_id' => $patientB->id])->assertRedirect();

        $this->assertSame([1, 2], QueueEntry::withoutGlobalScopes()->where('doctor_id', $doctorA->id)->pluck('token_number')->all());
        $this->assertSame([1], QueueEntry::withoutGlobalScopes()->where('doctor_id', $doctorB->id)->pluck('token_number')->all());
        $entry = QueueEntry::withoutGlobalScopes()->where('doctor_id', $doctorA->id)->where('token_number', 1)->firstOrFail();
        $this->assertNotEmpty($entry->public_token);
        $tokenResponse = $this->get(route('medical-token.show', [$restaurant->slug, $entry->public_token]));
        $tokenResponse->assertOk()
            ->assertSee('Your queue token')
            ->assertSee('http-equiv="refresh" content="10"', false)
            ->assertSee('Refresh now')
            ->assertSee('Last checked')
            ->assertSee('Dr. A')
            ->assertDontSee('Patient A');
        $this->assertStringContainsString('no-store', (string) $tokenResponse->headers->get('Cache-Control'));
        $this->get(route('manager.medical-queue.print', $entry))->assertOk()->assertSee('Patient A');

        $this->post(route('manager.medical-queue.next'), ['doctor_id' => $doctorA->id])->assertRedirect();
        $this->assertDatabaseHas('queue_entries', ['doctor_id' => $doctorA->id, 'token_number' => 1, 'status' => 'called']);
        $this->post(route('manager.medical-queue.next'), ['doctor_id' => $doctorA->id])->assertRedirect();
        $this->assertDatabaseHas('queue_entries', ['doctor_id' => $doctorA->id, 'token_number' => 2, 'status' => 'called']);
        $displayResponse = $this->get(route('medical-queue.display', $restaurant->slug));
        $displayResponse->assertOk()
            ->assertSee('Now Serving')
            ->assertSee('Refresh now')
            ->assertSee('Last checked')
            ->assertSee('Dr. A')
            ->assertSee('2')
            ->assertDontSee('Patient A')
            ->assertDontSee('Patient B');
        $this->assertStringContainsString('no-store', (string) $displayResponse->headers->get('Cache-Control'));

        $entry->load('doctor');
        $message = QueueNotificationService::calledMessage($entry);
        $this->assertSame('Queue token 1 is now being called by Dr. A. Please proceed.', $message);
        $this->assertStringNotContainsString('Patient A', $message);
        $this->assertStringNotContainsString('Patient B', $message);
        $this->assertStringNotContainsString('diagnosis', strtolower($message));
    }

    public function test_queue_notifications_require_tenant_and_patient_consent_before_dispatch(): void
    {
        [$restaurant, $manager] = $this->managerFor('clinic-notification-gate');
        $doctor = Doctor::withoutGlobalScopes()->create(['restaurant_id' => $restaurant->id, 'name' => 'Dr. Notify', 'specialty' => 'General', 'status' => 'active']);
        $patient = Patient::withoutGlobalScopes()->create([
            'restaurant_id' => $restaurant->id,
            'patient_number' => 'PAT-NOTIFY',
            'name' => 'Notification Patient',
            'phone' => '03000000007',
        ]);
        Queue::fake();
        $this->actingAs($manager);

        $this->post(route('manager.medical-queue.store'), [
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'notification_consent' => 1,
        ])->assertRedirect();
        $entry = QueueEntry::withoutGlobalScopes()->latest('id')->firstOrFail();
        $this->post(route('manager.medical-queue.next'), ['doctor_id' => $doctor->id])->assertRedirect();
        Queue::assertNothingPushed();

        $restaurant->update([
            'queue_notifications_enabled' => true,
            'queue_notification_channels' => ['sms', 'whatsapp'],
        ]);
        $patient->update(['notification_consent' => true]);
        $nextPatient = Patient::withoutGlobalScopes()->create([
            'restaurant_id' => $restaurant->id,
            'patient_number' => 'PAT-NOTIFY-2',
            'name' => 'Consenting Patient',
            'phone' => '03000000008',
            'notification_consent' => true,
        ]);
        $this->post(route('manager.medical-queue.store'), [
            'doctor_id' => $doctor->id,
            'patient_id' => $nextPatient->id,
            'notification_consent' => 1,
        ])->assertRedirect();
        $this->post(route('manager.medical-queue.next'), ['doctor_id' => $doctor->id])->assertRedirect();
        Queue::assertPushed(SendQueueNotification::class, 2);
        Queue::assertPushed(SendQueueNotification::class, function (SendQueueNotification $job) use ($restaurant, $nextPatient): bool {
            return $job->restaurantId === $restaurant->id
                && $job->channel === 'sms'
                && $job->queueEntryId !== 0;
        });
    }

    public function test_manager_can_configure_medical_queue_notification_channels(): void
    {
        [$restaurant, $manager] = $this->managerFor('clinic-notification-settings');
        $this->actingAs($manager);

        $this->get(route('manager.medical-notifications.edit'))
            ->assertOk()
            ->assertSee('Medical Queue Notifications');
        $this->patch(route('manager.medical-notifications.update'), [
            'queue_notifications_enabled' => 1,
            'queue_notification_channels' => ['sms'],
        ])->assertRedirect();

        $restaurant->refresh();
        $this->assertTrue($restaurant->queue_notifications_enabled);
        $this->assertSame(['sms'], $restaurant->queue_notification_channels);
    }

    public function test_medical_report_kpis_are_date_filtered_and_tenant_scoped(): void
    {
        [$restaurant, $manager] = $this->managerFor('clinic-report-kpis');
        [$otherRestaurant] = $this->managerFor('other-report-kpis');
        $doctor = Doctor::withoutGlobalScopes()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Dr. Report',
            'specialty' => 'General',
            'status' => 'active',
        ]);
        $patient = Patient::withoutGlobalScopes()->create([
            'restaurant_id' => $restaurant->id,
            'patient_number' => 'PAT-REPORT',
            'name' => 'Report Patient',
            'phone' => '03000000010',
        ]);
        $recentVisit = Visit::withoutGlobalScopes()->create([
            'restaurant_id' => $restaurant->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'checked_in_at' => now()->subDays(2),
        ]);
        $recentVisit->forceFill([
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ])->saveQuietly();
        $oldVisit = Visit::withoutGlobalScopes()->create([
            'restaurant_id' => $restaurant->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'status' => 'checked_in',
            'checked_in_at' => now()->subDays(10),
        ]);
        $oldVisit->forceFill([
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ])->saveQuietly();
        Doctor::withoutGlobalScopes()->create([
            'restaurant_id' => $otherRestaurant->id,
            'name' => 'Dr. Other Report',
            'specialty' => 'General',
            'status' => 'active',
        ]);
        Patient::withoutGlobalScopes()->create([
            'restaurant_id' => $otherRestaurant->id,
            'patient_number' => 'PAT-OTHER-REPORT',
            'name' => 'Other Report Patient',
        ]);
        $this->actingAs($manager);

        $this->get(route('manager.medical-reports.index', [
            'from' => now()->subDays(3)->toDateString(),
            'to' => now()->toDateString(),
        ]))
            ->assertOk()
            ->assertViewHas('stats', [
                'patients' => 1,
                'doctors' => 1,
                'visits' => 1,
                'completed_visits' => 1,
                'prescriptions' => 0,
                'dispensed' => 0,
                'pending_queue' => 0,
            ]);
    }

    public function test_queue_notification_job_uses_log_provider_and_standard_retry_configuration(): void
    {
        [$restaurant] = $this->managerFor('clinic-notification-job');
        $doctor = Doctor::withoutGlobalScopes()->create(['restaurant_id' => $restaurant->id, 'name' => 'Dr. Job', 'specialty' => 'General', 'status' => 'active']);
        $patient = Patient::withoutGlobalScopes()->create([
            'restaurant_id' => $restaurant->id,
            'patient_number' => 'PAT-JOB',
            'name' => 'Job Patient',
            'phone' => '03000000009',
            'notification_consent' => true,
        ]);
        $visit = Visit::withoutGlobalScopes()->create([
            'restaurant_id' => $restaurant->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'status' => 'in_progress',
            'checked_in_at' => now(),
        ]);
        $entry = QueueEntry::withoutGlobalScopes()->create([
            'restaurant_id' => $restaurant->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'visit_id' => $visit->id,
            'queue_date' => today(),
            'token_number' => 1,
            'public_token' => 'notification-job-token',
            'status' => 'called',
            'called_at' => now(),
        ]);
        $restaurant->update(['queue_notifications_enabled' => true, 'queue_notification_channels' => ['sms']]);
        Log::spy();

        $job = new SendQueueNotification($restaurant->id, $entry->id, 'sms');
        $job->handle();

        $this->assertSame(3, $job->tries);
        $this->assertSame([10, 30, 60], $job->backoff());
        Log::shouldHaveReceived('info')->withArgs(function (string $message, array $context): bool {
            return $message === 'Queue notification sent by log provider.'
                && $context['channel'] === 'sms'
                && $context['recipient'] === '03000000009'
                && ! str_contains($context['message'], 'Job Patient')
                && str_contains($context['message'], 'Queue token 1');
        });
    }

    public function test_twilio_provider_sends_sms_and_whatsapp_without_exposing_patient_data(): void
    {
        config()->set('services.medical_queue_notifications.driver', 'twilio');
        config()->set('services.twilio', [
            'sid' => 'AC123',
            'token' => 'secret-token',
            'from' => '+15550000001',
            'whatsapp_from' => '+15550000002',
        ]);
        Http::fake();

        app(\App\Contracts\NotificationProvider::class)->send('sms', '+923000000001', 'Queue token 4 for Dr. Test.');
        app(\App\Contracts\NotificationProvider::class)->send('whatsapp', '+923000000001', 'Queue token 4 for Dr. Test.');

        Http::assertSentCount(2);
        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://api.twilio.com/2010-04-01/Accounts/AC123/Messages.json'
                && $request['From'] === '+15550000001'
                && $request['To'] === '+923000000001';
        });
        Http::assertSent(function ($request): bool {
            return $request['From'] === 'whatsapp:+15550000002'
                && $request['To'] === 'whatsapp:+923000000001';
        });
    }

    public function test_failed_queue_notification_is_recorded_for_operations(): void
    {
        [$restaurant] = $this->managerFor('clinic-notification-failure');
        $job = new SendQueueNotification($restaurant->id, 44, 'sms');
        Log::spy();

        $job->failed(new RuntimeException('provider unavailable'));

        Log::shouldHaveReceived('error')->withArgs(function (string $message, array $context): bool {
            return $message === 'Queue notification delivery failed after retries.'
                && $context['queue_entry_id'] === 44
                && $context['channel'] === 'sms'
                && $context['exception'] === 'provider unavailable';
        });
    }

    public function test_reception_reconciles_a_todays_appointment_into_one_visit_and_token(): void
    {
        [$restaurant, $manager] = $this->managerFor('clinic-appointment');
        $doctor = Doctor::withoutGlobalScopes()->create(['restaurant_id' => $restaurant->id, 'name' => 'Dr. Appointment', 'specialty' => 'General', 'status' => 'active']);
        $patient = Patient::withoutGlobalScopes()->create(['restaurant_id' => $restaurant->id, 'patient_number' => 'PAT-APPT', 'name' => 'Appointment Patient']);
        $customer = Customer::withoutGlobalScopes()->create(['restaurant_id' => $restaurant->id, 'name' => 'Paying Customer', 'phone' => '03000000055', 'password' => bcrypt('secret')]);
        $appointment = Appointment::withoutGlobalScopes()->create([
            'restaurant_id' => $restaurant->id,
            'customer_id' => $customer->id,
            'patient_id' => $patient->id,
            'service_name' => 'Consultation',
            'starts_at' => today()->addHours(10),
            'ends_at' => today()->addHours(11),
            'status' => 'scheduled',
        ]);
        $this->actingAs($manager);

        $this->post(route('manager.medical-queue.store'), [
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'reason' => 'Scheduled arrival',
        ])->assertRedirect();

        $this->assertSame(1, Visit::withoutGlobalScopes()->where('appointment_id', $appointment->id)->count());
        $this->assertSame(1, QueueEntry::withoutGlobalScopes()->where('visit_id', Visit::withoutGlobalScopes()->where('appointment_id', $appointment->id)->value('id'))->count());
        $this->assertSame('confirmed', $appointment->fresh()->status);

        $this->post(route('manager.medical-queue.store'), [
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
        ])->assertStatus(422);
        $this->assertSame(1, Visit::withoutGlobalScopes()->where('appointment_id', $appointment->id)->count());
    }

    public function test_patient_history_is_read_only_and_tenant_scoped(): void
    {
        [$restaurant, $manager] = $this->managerFor('clinic-history');
        $otherRestaurant = Restaurant::create(['name' => 'Other Clinic', 'slug' => 'other-history', 'status' => 'active', 'enabled_modules' => ['medical']]);
        $patient = Patient::withoutGlobalScopes()->create(['restaurant_id' => $restaurant->id, 'patient_number' => 'PAT-HISTORY', 'name' => 'History Patient']);
        $otherPatient = Patient::withoutGlobalScopes()->create(['restaurant_id' => $otherRestaurant->id, 'patient_number' => 'PAT-OTHER', 'name' => 'Other Patient']);
        $doctor = Doctor::withoutGlobalScopes()->create(['restaurant_id' => $restaurant->id, 'name' => 'Dr. History', 'specialty' => 'General', 'status' => 'active']);
        Visit::withoutGlobalScopes()->create(['restaurant_id' => $restaurant->id, 'patient_id' => $patient->id, 'doctor_id' => $doctor->id, 'status' => 'completed', 'reason' => 'Follow-up', 'checked_in_at' => now()->subDay(), 'completed_at' => now()->subDay()]);
        $otherDoctor = Doctor::withoutGlobalScopes()->create(['restaurant_id' => $otherRestaurant->id, 'name' => 'Other Doctor', 'specialty' => 'General', 'status' => 'active']);
        Visit::withoutGlobalScopes()->create(['restaurant_id' => $otherRestaurant->id, 'patient_id' => $otherPatient->id, 'doctor_id' => $otherDoctor->id, 'status' => 'completed', 'checked_in_at' => now()->subDay()]);

        $this->actingAs($manager);
        $this->get(route('manager.patients.history', $patient))->assertOk()->assertSee('Follow-up')->assertSee('Dr. History')->assertDontSee('Other Doctor');
        $this->get(route('manager.patients.history', $otherPatient))->assertNotFound();
    }

    private function managerFor(string $slug): array
    {
        $restaurant = Restaurant::create([
            'name' => $slug,
            'slug' => $slug,
            'status' => 'active',
            'enabled_modules' => ['medical'],
        ]);
        $manager = User::create([
            'name' => 'Manager ' . $slug,
            'email' => $slug . '@example.com',
            'phone' => '03' . str_pad((string) random_int(100000000, 999999999), 9, '0'),
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => ['medical'],
        ]);

        return [$restaurant, $manager];
    }
}
