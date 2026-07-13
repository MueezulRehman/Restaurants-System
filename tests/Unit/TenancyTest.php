<?php

namespace Tests\Unit;

use App\Models\Restaurant;
use App\Support\Tenancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_configure_tenant_connection_switches_to_restaurant_database_configuration(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Tenant DB Test',
            'slug' => 'tenant-db-test',
            'status' => 'active',
            'db_connection' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
            ],
        ]);

        Tenancy::configureTenantConnection($restaurant);

        $this->assertSame('tenant', config('database.default'));
        $this->assertSame('sqlite', config('database.connections.tenant.driver'));
        $this->assertSame(':memory:', config('database.connections.tenant.database'));
        $this->assertSame('sqlite', DB::connection('tenant')->getDriverName());
    }
}
