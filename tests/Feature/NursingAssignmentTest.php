<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NursingAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_nursing_routes_require_the_nursing_module(): void
    {
        $restaurant = Restaurant::create(['name' => 'Clinic Tenant', 'slug' => 'clinic-nursing-tenant', 'status' => 'active', 'enabled_modules' => []]);
        $user = User::create(['name' => 'Manager', 'email' => 'nursing-block@example.com', 'phone' => '03000000012', 'role' => 'manager', 'restaurant_id' => $restaurant->id, 'password' => bcrypt('password')]);

        $this->actingAs($user)->get(route('manager.nursing.index'))->assertForbidden();
    }
}
