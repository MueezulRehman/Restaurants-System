<?php

namespace App\Console\Commands;

use App\Models\Restaurant;
use App\Support\Tenancy;
use Illuminate\Console\Command;
use Throwable;

/**
 * Run an Artisan command (or a closure-style description) for every tenant
 * (or a single one). Useful for maintenance tasks.
 *
 * Examples:
 *   php artisan tenants:run "cache:clear"
 *   php artisan tenants:run "migrate --force" --id=5
 *   php artisan tenants:run "db:seed --class=SomeSeeder" --only-with-db
 *
 * @author Mueez Ul Rehman
 */
class RunForTenantsCommand extends Command
{
    protected $signature = 'tenants:run
                            {command_line : The artisan command to run inside each tenant context}
                            {--id= : Only run for this restaurant id}
                            {--only-with-db : Skip businesses that have no tenant database yet}';

    protected $description = 'Run an Artisan command inside each Codeibex tenant database context';

    public function handle(): int
    {
        $commandLine = $this->argument('command_line');
        $query = Restaurant::query()->orderBy('id');

        if ($id = $this->option('id')) {
            $query->where('id', $id);
        }

        $restaurants = $query->get();

        if ($restaurants->isEmpty()) {
            $this->warn('No restaurants found.');

            return self::SUCCESS;
        }

        $ok = 0;
        $fail = 0;
        $skipped = 0;

        foreach ($restaurants as $restaurant) {
            if ($this->option('only-with-db') && ! $restaurant->hasTenantDatabase()) {
                $this->line("→ #{$restaurant->id} {$restaurant->name} — skipped (no tenant DB)");
                $skipped++;
                continue;
            }

            $this->line("→ #{$restaurant->id} {$restaurant->name}");

            try {
                Tenancy::runFor($restaurant, function () use ($commandLine) {
                    $this->call($commandLine);
                });

                $this->info('  OK');
                $ok++;
            } catch (Throwable $e) {
                $this->error('  FAILED: ' . $e->getMessage());
                $fail++;
            } finally {
                Tenancy::end();
            }
        }

        $this->newLine();
        $this->info("Done. Success: {$ok}, Failed: {$fail}, Skipped: {$skipped}");

        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }
}
