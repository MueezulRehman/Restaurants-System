<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\User;
use App\Models\BusinessType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_and_update_a_staff_type(): void
    {
        [$restaurant, $manager] = $this->managerFor('staff-types');
        $this->actingAs($manager);

        $this->post(route('manager.staff.store'), [
            'name' => 'Nurse Ayesha',
            'email' => 'nurse@example.com',
            'phone' => '03000000101',
            'role' => 'staff',
            'staff_type' => 'nurse',
            'password' => 'password123',
        ])->assertRedirect(route('manager.staff.index'));

        $staff = User::where('restaurant_id', $restaurant->id)->where('email', 'nurse@example.com')->firstOrFail();
        $this->assertSame('nurse', $staff->staff_type);

        $this->patch(route('manager.staff.update', $staff), [
            'name' => 'Pharmacist Ayesha',
            'email' => 'nurse@example.com',
            'phone' => '03000000101',
            'role' => 'staff',
            'staff_type' => 'pharmacist',
        ])->assertRedirect(route('manager.staff.index'));

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'staff_type' => 'pharmacist',
        ]);
    }

    public function test_staff_type_must_use_the_approved_plain_string_list(): void
    {
        [, $manager] = $this->managerFor('staff-type-validation');
        $this->actingAs($manager);

        $this->from(route('manager.staff.create'))
            ->post(route('manager.staff.store'), [
                'name' => 'Unsupported Role',
                'email' => 'unsupported@example.com',
                'phone' => '03000000102',
                'role' => 'staff',
                'staff_type' => 'surgeon',
                'password' => 'password123',
            ])
            ->assertRedirect(route('manager.staff.create'))
            ->assertSessionHasErrors('staff_type');
    }

    public function test_staff_type_field_is_hidden_for_restaurants(): void
    {
        [$restaurant, $manager] = $this->managerFor('restaurant-staff-types', 'Fast Food');
        $this->actingAs($manager);

        $this->get(route('manager.staff.create'))
            ->assertOk()
            ->assertDontSee('Staff Type');
    }

    private function managerFor(string $slug, string $businessTypeName = 'Medical Store'): array
    {
        $businessType = BusinessType::create(['name' => $businessTypeName, 'is_active' => true]);
        $restaurant = Restaurant::create([
            'name' => $slug,
            'slug' => $slug,
            'status' => 'active',
            'enabled_modules' => ['medical'],
            'business_type_id' => $businessType->id,
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
