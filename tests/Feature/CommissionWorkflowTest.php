<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AppointmentController;
use App\Models\Appointment;
use App\Models\CommissionEarning;
use App\Models\CommissionRule;
use App\Models\Customer;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class CommissionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_completed_appointment_generates_one_staff_commission(): void
    {
        $restaurant = Restaurant::create(['name' => 'Commission Salon', 'slug' => 'commission-salon', 'status' => 'active', 'plan' => 'basic']);
        $manager = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000023']);
        $staff = User::factory()->create(['role' => 'staff', 'restaurant_id' => $restaurant->id, 'phone' => '03000000024']);
        $customer = Customer::create(['restaurant_id' => $restaurant->id, 'name' => 'Commission Client', 'phone' => '03000000025', 'password' => bcrypt('secret')]);
        $appointment = Appointment::create(['restaurant_id' => $restaurant->id, 'customer_id' => $customer->id, 'staff_id' => $staff->id, 'service_name' => 'Color treatment', 'starts_at' => '2026-10-01 10:00', 'ends_at' => '2026-10-01 12:00', 'status' => 'confirmed', 'price' => 2000]);
        CommissionRule::create(['restaurant_id' => $restaurant->id, 'staff_id' => $staff->id, 'type' => 'percent', 'value' => 10, 'is_active' => true]);

        $this->actingAs($manager, 'web');
        $request = Request::create('/manager/appointments/' . $appointment->id . '/status', 'PATCH', ['status' => 'completed']);
        app(AppointmentController::class)->updateStatus($request, $appointment);
        app(AppointmentController::class)->updateStatus($request, $appointment->fresh());

        $this->assertDatabaseHas('commission_earnings', ['appointment_id' => $appointment->id, 'staff_id' => $staff->id, 'commission_amount' => 200, 'status' => 'pending']);
        $this->assertSame(1, CommissionEarning::where('appointment_id', $appointment->id)->count());
    }
}
