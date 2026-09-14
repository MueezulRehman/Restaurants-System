<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\ModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_hospital_manager_can_manage_tenant_departments(): void
    {
        ModuleService::ensureDefaults();
        $restaurant = Restaurant::create([
            'name' => 'Professional Hospital',
            'slug' => 'professional-hospital',
            'status' => 'active',
            'enabled_modules' => ['hospital-departments'],
        ]);
        $user = User::create([
            'name' => 'Hospital Manager',
            'email' => 'department-manager@example.com',
            'phone' => '03000000001',
            'restaurant_id' => $restaurant->id,
            'role' => 'manager',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->post(route('manager.departments.store'), [
            'name' => 'General Medicine',
            'code' => 'GEN-MED',
            'description' => 'Adult inpatient care',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('manager.departments.index'));
        $this->assertDatabaseHas('departments', [
            'restaurant_id' => $restaurant->id,
            'code' => 'GEN-MED',
        ]);
    }

    public function test_department_routes_are_blocked_without_the_module(): void
    {
        $restaurant = Restaurant::create(['name' => 'No Departments', 'slug' => 'no-departments', 'status' => 'active', 'enabled_modules' => []]);
        $user = User::create([
            'name' => 'Blocked Manager',
            'email' => 'blocked-department-manager@example.com',
            'phone' => '03000000002',
            'restaurant_id' => $restaurant->id,
            'role' => 'manager',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user)
            ->get(route('manager.departments.index'))
            ->assertForbidden();
    }

    public function test_department_records_are_tenant_scoped(): void
    {
        $first = Restaurant::create(['name' => 'Hospital One', 'slug' => 'hospital-one', 'status' => 'active', 'enabled_modules' => ['hospital-departments']]);
        $second = Restaurant::create(['name' => 'Hospital Two', 'slug' => 'hospital-two', 'status' => 'active', 'enabled_modules' => ['hospital-departments']]);
        $user = User::create(['name' => 'Scoped Manager', 'email' => 'scoped-manager@example.com', 'phone' => '03000000003', 'restaurant_id' => $first->id, 'role' => 'manager', 'password' => bcrypt('password')]);
        $department = Department::create([
            'restaurant_id' => $second->id,
            'name' => 'Emergency',
            'code' => 'ER',
        ]);

        $this->actingAs($user)
            ->get(route('manager.departments.edit', $department))
            ->assertNotFound();
    }
}
