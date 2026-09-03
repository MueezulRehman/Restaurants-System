<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant reports.user_id may reference central users — make nullable and drop rigid FK if needed.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reports')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE reports DROP FOREIGN KEY reports_user_id_foreign');
            } catch (\Throwable $e) {
                // already dropped / different name
            }
            try {
                DB::statement('ALTER TABLE reports MODIFY user_id BIGINT UNSIGNED NULL');
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    public function down(): void
    {
        // non-destructive
    }
};
