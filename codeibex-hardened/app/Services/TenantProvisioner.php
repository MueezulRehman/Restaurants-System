<?php

namespace App\Services;

use App\Models\Restaurant;
use Database\Seeders\TenantDatabaseSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Codeibex Tenant Database Provisioner (Hardened)
 *
 * - Creates one MySQL/SQLite database per business
 * - Runs the FULL tenant schema (database/tenant_migrations)
 * - Optionally seeds demo data
 * - Safely switches connections and always restores central default
 *
 * Module access stays on the central restaurants.enabled_modules column.
 *
 * @author Mueez Ul Rehman
 */
class TenantProvisioner
{
    /**
     * Ensure the restaurant has a dedicated database with full schema.
     * Safe to call multiple times (idempotent migrate).
     */
    public function provision(Restaurant $restaurant, bool $seed = true): bool
    {
        $originalDefault = config('database.default');

        try {
            if (! $restaurant->hasTenantDatabase()) {
                $this->createDatabaseAndAttachConfig($restaurant);
                $restaurant->refresh();
            }

            $this->configureConnection($restaurant);

            // Temporarily make tenant the default so Eloquent / seeders hit the right DB
            config(['database.default' => config('tenancy.connection', 'tenant')]);

            $this->runMigrations();

            if ($seed && config('tenancy.seed_after_migrate', true)) {
                $this->seed($restaurant);
            }

            Log::info('Codeibex tenant provisioned', [
                'restaurant_id' => $restaurant->id,
                'database' => $restaurant->getTenantDatabaseConfig()['database'] ?? null,
            ]);

            return true;
        } catch (Throwable $e) {
            Log::error('Codeibex tenant provision failed', [
                'restaurant_id' => $restaurant->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        } finally {
            // Always restore central connection
            $this->restoreCentralConnection($originalDefault);
        }
    }

    /**
     * Create database and store encrypted connection config on the restaurant.
     */
    public function createDatabaseAndAttachConfig(Restaurant $restaurant): void
    {
        $dbName = $this->databaseNameFor($restaurant);
        $template = config('tenancy.template', []);
        $driver = $template['driver'] ?? 'mysql';

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $this->createMysqlDatabase($dbName, $template);
        } elseif ($driver === 'sqlite') {
            $this->createSqliteDatabase($dbName);
        } else {
            throw new \RuntimeException("Unsupported tenant driver [{$driver}] for Codeibex.");
        }

        $config = array_merge($template, [
            'database' => $dbName,
        ]);

        if ($driver === 'sqlite') {
            $config['database'] = database_path('tenants/' . $dbName . '.sqlite');
        }

        $restaurant->forceFill([
            'db_connection' => $config,
        ])->save();

        Log::info('Codeibex tenant database created', [
            'restaurant_id' => $restaurant->id,
            'database' => $config['database'],
            'driver' => $driver,
        ]);
    }

    /**
     * Drop a tenant database (use with extreme care).
     */
    public function dropDatabase(Restaurant $restaurant): void
    {
        if (! $restaurant->hasTenantDatabase()) {
            return;
        }

        $config = $restaurant->getTenantDatabaseConfig();
        $driver = $config['driver'] ?? 'mysql';
        $dbName = $config['database'] ?? null;

        if (! $dbName) {
            return;
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $safe = str_replace('`', '``', basename($dbName)); // safety
            $pdo = DB::connection(config('tenancy.central_connection', env('DB_CONNECTION', 'mysql')))->getPdo();
            $pdo->exec("DROP DATABASE IF EXISTS `{$safe}`");
        } elseif ($driver === 'sqlite' && is_file($dbName)) {
            @unlink($dbName);
        }

        $restaurant->forceFill(['db_connection' => null])->save();

        Log::warning('Codeibex tenant database dropped', [
            'restaurant_id' => $restaurant->id,
            'database' => $dbName,
        ]);
    }

    protected function createMysqlDatabase(string $dbName, array $template): void
    {
        $charset = $template['charset'] ?? 'utf8mb4';
        $collation = $template['collation'] ?? 'utf8mb4_unicode_ci';

        $central = config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));
        $pdo = DB::connection($central)->getPdo();
        $safe = str_replace('`', '``', $dbName);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$safe}` CHARACTER SET {$charset} COLLATE {$collation}");
    }

    protected function createSqliteDatabase(string $dbName): void
    {
        $dir = database_path('tenants');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $path = $dir . '/' . $dbName . '.sqlite';
        if (! file_exists($path)) {
            touch($path);
        }
    }

    public function databaseNameFor(Restaurant $restaurant): string
    {
        $prefix = config('tenancy.database_prefix', 'codeibex_tenant_');

        return $prefix . $restaurant->id;
    }

    /**
     * Register the tenant connection in the container and reconnect.
     */
    public function configureConnection(Restaurant $restaurant): void
    {
        $name = config('tenancy.connection', 'tenant');
        $config = $restaurant->getTenantDatabaseConfig();

        if (empty($config['database'])) {
            throw new \RuntimeException("Restaurant #{$restaurant->id} has no tenant database configured.");
        }

        config(["database.connections.{$name}" => $config]);
        DB::purge($name);
        DB::reconnect($name);
    }

    public function runMigrations(): void
    {
        $name = config('tenancy.connection', 'tenant');
        $relative = config('tenancy.migrations_path', 'database/tenant_migrations');
        $path = base_path($relative);

        if (! is_dir($path)) {
            throw new \RuntimeException("Tenant migrations path not found: {$path}");
        }

        Artisan::call('migrate', [
            '--database' => $name,
            '--path' => $relative,
            '--force' => true,
        ]);
    }

    public function seed(Restaurant $restaurant): void
    {
        $this->configureConnection($restaurant);
        $original = config('database.default');
        config(['database.default' => config('tenancy.connection', 'tenant')]);

        try {
            $seeder = app(TenantDatabaseSeeder::class);

            if (method_exists($seeder, 'setRestaurant')) {
                $seeder->setRestaurant($restaurant);
            }

            $seeder->run();
        } finally {
            config(['database.default' => $original]);
        }
    }

    /**
     * Switch the default connection to this tenant for the rest of the request.
     */
    public function useAsDefault(Restaurant $restaurant): void
    {
        $this->configureConnection($restaurant);
        config(['database.default' => config('tenancy.connection', 'tenant')]);
    }

    /**
     * Restore the central (or previously remembered) connection as default
     * and purge the tenant connection to free the PDO handle.
     */
    public function restoreCentralConnection(?string $originalDefault = null): void
    {
        $tenantConnection = config('tenancy.connection', 'tenant');
        $restoreTo = $originalDefault
            ?? config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));

        try {
            DB::purge($tenantConnection);
        } catch (Throwable $e) {
            // ignore
        }

        config(['database.default' => $restoreTo]);
    }

    /**
     * Check whether the physical database currently exists.
     */
    public function databaseExists(Restaurant $restaurant): bool
    {
        if (! $restaurant->hasTenantDatabase()) {
            return false;
        }

        $config = $restaurant->getTenantDatabaseConfig();
        $driver = $config['driver'] ?? 'mysql';
        $dbName = $config['database'] ?? null;

        if (! $dbName) {
            return false;
        }

        try {
            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                $central = config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));
                $result = DB::connection($central)->select(
                    'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?',
                    [$dbName]
                );

                return count($result) > 0;
            }

            if ($driver === 'sqlite') {
                return is_file($dbName);
            }
        } catch (Throwable $e) {
            Log::warning('Codeibex: could not check tenant database existence', [
                'restaurant_id' => $restaurant->id,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }
}
