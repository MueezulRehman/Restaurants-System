<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ServiceCaseController;
use App\Models\Customer;
use App\Models\Restaurant;
use App\Models\ServiceCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ServiceCaseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_repair_case_can_be_assigned_and_completed_with_parts_and_final_cost(): void
    {
        $restaurant = Restaurant::create(['name' => 'Repair Test', 'slug' => 'repair-test', 'status' => 'active', 'plan' => 'basic']);
        $manager = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000008']);
        $technician = User::factory()->create(['role' => 'staff', 'restaurant_id' => $restaurant->id, 'phone' => '03000000009']);
        $customer = Customer::create(['restaurant_id' => $restaurant->id, 'name' => 'Repair Customer', 'phone' => '03000000010', 'password' => bcrypt('secret')]);

        $this->actingAs($manager, 'web');
        $response = app(ServiceCaseController::class)->store(Request::create('/manager/service-cases', 'POST', [
            'case_type' => 'repair',
            'customer_id' => $customer->id,
            'assigned_to' => $technician->id,
            'serial_number' => 'IMEI-001',
            'title' => 'Screen replacement',
            'description' => 'Cracked display',
            'estimated_cost' => 5000,
            'due_at' => '2026-10-05',
        ]));

        $this->assertTrue($response->isRedirect());
        $case = ServiceCase::firstOrFail();
        $this->assertSame($technician->id, $case->assigned_to);

        $updateResponse = app(ServiceCaseController::class)->update(Request::create('/manager/service-cases/' . $case->id, 'PATCH', [
            'status' => 'ready',
            'assigned_to' => $technician->id,
            'parts_used' => 'OLED display, adhesive frame',
            'final_cost' => 5500,
            'resolution' => 'Display replaced and tested',
        ]), $case);

        $this->assertTrue($updateResponse->isRedirect());
        $case = $case->fresh();
        $this->assertSame('ready', $case->status);
        $this->assertSame('OLED display, adhesive frame', $case->parts_used);
        $this->assertSame(5500.0, (float) $case->final_cost);
    }

    public function test_service_tickets_module_can_open_the_repair_queue(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Ticket Queue Test',
            'slug' => 'ticket-queue-test',
            'status' => 'active',
            'enabled_modules' => ['service-tickets'],
        ]);
        $restaurant->subscription()->create(['status' => 'active', 'billing_cycle' => 'monthly']);
        $manager = User::factory()->create([
            'role' => 'admin',
            'restaurant_id' => $restaurant->id,
            'phone' => '03000000011',
        ]);

        $this->actingAs($manager, 'web')
            ->get(route('manager.service-cases.index'))
            ->assertOk()
            ->assertSee('Warranty &amp; Repairs', false);
    }
}
