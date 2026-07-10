<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSuperAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_modules_page(): void
    {
        $user = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'super@example.com',
            'phone' => '03001234567',
        ]);

        $response = $this->actingAs($user)->get(route('admin.modules.index'));

        $response->assertStatus(200);
    }

    public function test_super_admin_can_open_business_types_page(): void
    {
        $user = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'super2@example.com',
            'phone' => '03007654321',
        ]);

        $response = $this->actingAs($user)->get(route('admin.business-types.index'));

        $response->assertStatus(200);
    }
}
