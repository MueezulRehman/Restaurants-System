<?php

namespace App\Console\Commands;

use App\Models\Restaurant;
use App\Services\TenantProvisioner;
use Illuminate\Console\Command;
use Throwable;

/**
 * Provision (create DB + full migrate + seed) one or all businesses.
 *
 * @author Mueez Ul Rehman
 */
class ProvisionTenantCommand extends Command
{
    protected $signature = 'tenants:provision
                            {restaurant_id? : Restaurant id (omit for all without a DB yet)}
                            {--all : Provision every restaurant}
                            {--seed : Run tenant seeder}';

    protected $description = 'Create Codeibex tenant database and apply full schema';

    public function handle(TenantProvisioner $provisioner): int
    {
        if ($this->option('all')) {
            $restaurants = Restaurant::orderBy('id')->get();
        } elseif ($id = $this->argument('restaurant_id')) {
            $restaurants = Restaurant::where('id', $id)->get();
        } else {
            $restaurants = Restaurant::orderBy('id')->get()->filter(
                fn (Restaurant $r) => ! $r->hasTenantDatabase()
            );
        }

        if ($restaurants->isEmpty()) {
            $this->warn('Nothing to provision.');

            return self::SUCCESS;
        }

        foreach ($restaurants as $restaurant) {
            $this->line("Provisioning #{$restaurant->id} {$restaurant->name}…");

            try {
                $provisioner->provision($restaurant, (bool) $this->option('seed'));
                $db = $restaurant->fresh()->getTenantDatabaseConfig()['database'] ?? '?';
                $this->info("  Database: {$db}");
            } catch (Throwable $e) {
                $this->error('  ' . $e->getMessage());

                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
