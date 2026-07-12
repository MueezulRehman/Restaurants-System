<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class ReportAccessTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    public function test_manager_can_view_a_report_for_a_module_they_have_access_to(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Report Access Test',
            'slug' => 'report-access-test',
            'status' => 'active',
            'enabled_modules' => ['reports', 'orders'],
        ]);

        $user = User::create([
            'name' => 'Report Manager',
            'email' => 'report-manager@example.com',
            'phone' => '1234567892',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => ['reports', 'orders'],
        ]);

        $this->actingAs($user);

        $report = Report::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
            'type' => 'orders',
            'name' => 'Orders summary',
            'filters' => ['date_from' => '2026-07-01', 'date_to' => '2026-07-12'],
            'data_snapshot' => ['orders' => 5],
            'generated_at' => now(),
        ]);

        $response = $this->get(route('manager.reports.show', $report));

        $response->assertOk();
    }

    public function test_report_creation_page_lists_only_types_for_modules_the_user_can_access(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Report Types Test',
            'slug' => 'report-types-test',
            'status' => 'active',
            'enabled_modules' => ['reports', 'orders', 'stock'],
        ]);

        $user = User::create([
            'name' => 'Limited Manager',
            'email' => 'limited-manager@example.com',
            'phone' => '1234567893',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => ['reports', 'orders'],
        ]);

        $this->actingAs($user);

        $response = $this->get(route('manager.reports.create'));

        $response->assertOk();
        $response->assertSee('Orders');
        $response->assertSee('Sales');
        $response->assertDontSee('Inventory');
        $response->assertDontSee('Financial');
    }
}
