<?php

namespace App\Console\Commands;

use App\Models\Restaurant;
use App\Services\TenantProvisioner;
use Illuminate\Console\Command;
use Throwable;

/**
 * Run tenant migrations on every business database (or one by id).
 *
 * @author Mueez Ul Rehman
 */
class MigrateTenantsCommand extends Command
{
    protected $signature = 'tenants:migrate
                            {restaurant_id? : Optional restaurant id}
                            {--seed : Seed after migrate}
                            {--fresh : Drop all tables and re-migrate (destructive)}';

    protected $description = 'Migrate Codeibex tenant databases (full schema per business)';

    public function handle(TenantProvisioner $provisioner): int
    {
        $query = Restaurant::query()->orderBy('id');

        if ($id = $this->argument('restaurant_id')) {
            $query->where('id', $id);
        }

        $restaurants = $query->get();

        if ($restaurants->isEmpty()) {
            $this->warn('No restaurants found.');

            return self::SUCCESS;
        }

        $ok = 0;
        $fail = 0;

        foreach ($restaurants as $restaurant) {
            $this->line("→ Business #{$restaurant->id} {$restaurant->name}");

            try {
                if (! $restaurant->hasTenantDatabase()) {
                    $this->comment('  Creating database…');
                    $provisioner->createDatabaseAndAttachConfig($restaurant);
                    $restaurant->refresh();
                }

                $provisioner->configureConnection($restaurant);

                if ($this->option('fresh')) {
                    $this->warn('  migrate:fresh (destructive)');
                    \Illuminate\Support\Facades\Artisan::call('migrate:fresh', [
                        '--database' => config('tenancy.connection', 'tenant'),
                        '--path' => config('tenancy.migrations_path', 'database/tenant_migrations'),
                        '--force' => true,
                    ]);
                } else {
                    $provisioner->runMigrations();
                }

                if ($this->option('seed')) {
                    $provisioner->seed($restaurant);
                }

                $this->info('  OK');
                $ok++;
            } catch (Throwable $e) {
                $this->error('  FAILED: ' . $e->getMessage());
                $fail++;
            }
        }

        $this->newLine();
        $this->info("Done. Success: {$ok}, Failed: {$fail}");

        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }
}
