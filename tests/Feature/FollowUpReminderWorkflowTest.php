<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\FollowUpReminder;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowUpReminderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_clinic_manager_can_schedule_and_complete_a_follow_up(): void
    {
        $restaurant = Restaurant::create(['name' => 'Clinic Test', 'slug' => 'clinic-test', 'status' => 'active', 'enabled_modules' => ['follow-up-reminders']]);
        $restaurant->subscription()->create(['status' => 'active', 'billing_cycle' => 'monthly']);
        $manager = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000030']);
        $customer = Customer::create(['restaurant_id' => $restaurant->id, 'name' => 'Patient Followup', 'phone' => '03000000031', 'password' => bcrypt('secret')]);

        $this->actingAs($manager, 'web')->post(route('manager.follow-up-reminders.store'), ['customer_id' => $customer->id, 'due_at' => now()->addWeek()->format('Y-m-d H:i'), 'note' => 'Review treatment'])->assertRedirect();
        $reminder = FollowUpReminder::firstOrFail();
        $this->assertSame('scheduled', $reminder->status);

        $this->patch(route('manager.follow-up-reminders.update', $reminder), ['status' => 'completed'])->assertRedirect();
        $reminder = $reminder->fresh();
        $this->assertSame('completed', $reminder->status);
        $this->assertNotNull($reminder->completed_at);
    }

    public function test_follow_up_cannot_link_a_patient_from_another_restaurant(): void
    {
        $restaurant = Restaurant::create(['name' => 'Clinic A', 'slug' => 'clinic-a', 'status' => 'active', 'enabled_modules' => ['follow-up-reminders']]);
        $other = Restaurant::create(['name' => 'Clinic B', 'slug' => 'clinic-b', 'status' => 'active']);
        $restaurant->subscription()->create(['status' => 'active', 'billing_cycle' => 'monthly']);
        $manager = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000032']);
        $customer = Customer::create(['restaurant_id' => $other->id, 'name' => 'Other Patient', 'phone' => '03000000033', 'password' => bcrypt('secret')]);

        $this->actingAs($manager, 'web')->from(route('manager.follow-up-reminders.index'))
            ->post(route('manager.follow-up-reminders.store'), ['customer_id' => $customer->id, 'due_at' => now()->addWeek()->format('Y-m-d H:i')])
            ->assertSessionHasErrors('customer_id');

        $this->assertSame(0, FollowUpReminder::count());
    }
}
