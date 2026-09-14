<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HospitalBedAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_bed_assignment_route_requires_the_admissions_module(): void
    {
        $restaurant = Restaurant::create(['name' => 'Clinic Tenant', 'slug' => 'clinic-bed-tenant', 'status' => 'active', 'enabled_modules' => ['hospital-wards-beds']]);
        $user = User::create(['name' => 'Manager', 'email' => 'bed-block@example.com', 'phone' => '03000000011', 'role' => 'manager', 'restaurant_id' => $restaurant->id, 'password' => bcrypt('password')]);

        $this->actingAs($user)->get(route('manager.admissions.index'))->assertForbidden();
    }
}
