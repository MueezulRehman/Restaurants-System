<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VitalSignsTest extends TestCase
{
    use RefreshDatabase;

    public function test_vital_signs_routes_require_the_module(): void
    {
        $restaurant = Restaurant::create(['name' => 'Clinic Tenant', 'slug' => 'clinic-vitals-tenant', 'status' => 'active', 'enabled_modules' => []]);
        $user = User::create(['name' => 'Manager', 'email' => 'vitals-block@example.com', 'phone' => '03000000013', 'role' => 'manager', 'restaurant_id' => $restaurant->id, 'password' => bcrypt('password')]);
        $this->actingAs($user)->get(route('manager.vitals.index'))->assertForbidden();
    }
}
