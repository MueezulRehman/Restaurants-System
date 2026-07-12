<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSidebarTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_dashboard_shows_register_business_link(): void
    {
        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'super@example.com',
            'phone' => '03001234567',
            'role' => 'super_admin',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->actingAs($user, 'web')
            ->get('/admin/dashboard')
            ->assertStatus(200)
            ->assertSee('Register Restaurant / Business');
    }
}
