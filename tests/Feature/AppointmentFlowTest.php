<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AppointmentController;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AppointmentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_book_and_update_a_customer_appointment(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Salon Test',
            'slug' => 'salon-test',
            'status' => 'active',
            'plan' => 'basic',
        ]);
        $user = User::factory()->create([
            'role' => 'admin',
            'restaurant_id' => $restaurant->id,
            'phone' => '03000000003',
        ]);
        $customer = Customer::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Salon Customer',
            'phone' => '03000000004',
            'password' => bcrypt('secret'),
        ]);
        $staff = User::factory()->create([
            'role' => 'staff',
            'restaurant_id' => $restaurant->id,
            'phone' => '03000000005',
        ]);

        $this->actingAs($user, 'web');
        $response = app(AppointmentController::class)->store(Request::create('/manager/appointments', 'POST', [
            'customer_id' => $customer->id,
            'staff_id' => $staff->id,
            'service_name' => 'Haircut and styling',
            'starts_at' => '2026-10-01 10:00',
            'ends_at' => '2026-10-01 11:00',
            'status' => 'scheduled',
            'price' => 2500,
            'notes' => 'First visit',
        ]));

        $this->assertTrue($response->isRedirect());
        $appointment = Appointment::firstOrFail();
        $this->assertSame('scheduled', $appointment->status);
        $this->assertSame($customer->id, $appointment->customer_id);
        $this->assertSame($staff->id, $appointment->staff_id);

        $statusResponse = app(AppointmentController::class)->updateStatus(
            Request::create('/manager/appointments/' . $appointment->id . '/status', 'PATCH', ['status' => 'completed']),
            $appointment
        );

        $this->assertTrue($statusResponse->isRedirect());
        $this->assertSame('completed', $appointment->fresh()->status);
    }

    public function test_booking_rejects_a_customer_from_another_restaurant(): void
    {
        $restaurant = Restaurant::create(['name' => 'Salon A', 'slug' => 'salon-a', 'status' => 'active', 'plan' => 'basic']);
        $otherRestaurant = Restaurant::create(['name' => 'Salon B', 'slug' => 'salon-b', 'status' => 'active', 'plan' => 'basic']);
        $user = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000006']);
        $customer = Customer::create(['restaurant_id' => $otherRestaurant->id, 'name' => 'Other Customer', 'phone' => '03000000007', 'password' => bcrypt('secret')]);

        $this->actingAs($user, 'web');
        try {
            app(AppointmentController::class)->store(Request::create('/manager/appointments', 'POST', [
                'customer_id' => $customer->id,
                'service_name' => 'Facial',
                'starts_at' => '2026-10-01 10:00',
                'ends_at' => '2026-10-01 11:00',
                'status' => 'scheduled',
            ]));
            $this->fail('A customer from another restaurant should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('customer_id', $exception->errors());
        }

        $this->assertSame(0, Appointment::count());
    }

    public function test_salon_appointment_module_can_open_the_booking_queue(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Salon Queue Test',
            'slug' => 'salon-queue-test',
            'status' => 'active',
            'enabled_modules' => ['appointments'],
        ]);
        $restaurant->subscription()->create(['status' => 'active', 'billing_cycle' => 'monthly']);
        $manager = User::factory()->create([
            'role' => 'admin',
            'restaurant_id' => $restaurant->id,
            'phone' => '03000000027',
        ]);

        $this->actingAs($manager, 'web')
            ->get(route('manager.appointments.index'))
            ->assertOk()
            ->assertSee('Appointments');
    }
}
