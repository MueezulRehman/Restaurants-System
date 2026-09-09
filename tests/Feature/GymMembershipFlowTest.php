<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\GymController;
use App\Models\Customer;
use App\Models\GymCheckIn;
use App\Models\GymMembership;
use App\Models\GymPlan;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class GymMembershipFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_be_activated_renewed_and_checked_in(): void
    {
        [$user, $customer] = $this->makeGymAccount();
        $plan = GymPlan::create(['restaurant_id' => $user->restaurant_id, 'name' => 'Monthly', 'duration_days' => 30, 'price' => 3000, 'is_active' => true]);
        $this->actingAs($user, 'web');

        $response = app(GymController::class)->storeMembership(Request::create('/manager/gym/memberships', 'POST', [
            'customer_id' => $customer->id,
            'gym_plan_id' => $plan->id,
            'starts_at' => '2026-10-01',
            'amount_paid' => 3000,
        ]));
        $this->assertTrue($response->isRedirect());

        $membership = GymMembership::firstOrFail();
        $this->assertSame('2026-10-30', $membership->ends_at->toDateString());

        app(GymController::class)->renew(Request::create('/manager/gym/memberships/1/renew', 'POST', ['amount_paid' => 3000]), $membership);
        $membership->refresh();
        $this->assertSame('2026-11-29', $membership->ends_at->toDateString());

        app(GymController::class)->checkIn($membership);
        $this->assertSame(1, GymCheckIn::count());
    }

    public function test_expired_member_cannot_check_in(): void
    {
        [$user, $customer] = $this->makeGymAccount();
        $plan = GymPlan::create(['restaurant_id' => $user->restaurant_id, 'name' => 'Expired Plan', 'duration_days' => 1, 'price' => 100, 'is_active' => true]);
        $membership = GymMembership::create([
            'restaurant_id' => $user->restaurant_id,
            'customer_id' => $customer->id,
            'gym_plan_id' => $plan->id,
            'starts_at' => now()->subDays(3),
            'ends_at' => now()->subDay(),
            'status' => 'expired',
            'amount_paid' => 100,
        ]);
        $this->actingAs($user, 'web');

        try {
            app(GymController::class)->checkIn($membership);
            $this->fail('An expired membership should not be checked in.');
        } catch (HttpException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
        }

        $this->assertSame(0, GymCheckIn::count());
    }

    private function makeGymAccount(): array
    {
        $restaurant = Restaurant::create(['name' => 'Gym Test', 'slug' => 'gym-test-' . uniqid(), 'status' => 'active', 'plan' => 'basic']);
        $user = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000011']);
        $customer = Customer::create(['restaurant_id' => $restaurant->id, 'name' => 'Gym Member', 'phone' => '03000000012', 'password' => bcrypt('secret')]);

        return [$user, $customer];
    }
}
