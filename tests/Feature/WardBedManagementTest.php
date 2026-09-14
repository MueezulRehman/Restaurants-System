<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WardBedManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_ward_routes_require_the_wards_module(): void
    {
        $restaurant = Restaurant::create(['name' => 'Clinic Tenant', 'slug' => 'clinic-ward-tenant', 'status' => 'active', 'enabled_modules' => []]);
        $user = User::create(['name' => 'Manager', 'email' => 'ward-block@example.com', 'phone' => '03000000010', 'role' => 'manager', 'restaurant_id' => $restaurant->id, 'password' => bcrypt('password')]);
        $this->actingAs($user)->get(route('manager.wards.index'))->assertForbidden();
    }
}
