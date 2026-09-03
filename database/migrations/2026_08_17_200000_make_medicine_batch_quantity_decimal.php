<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('medicine_batches', 'quantity')) {
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE medicine_batches MODIFY quantity DECIMAL(14,3) NOT NULL DEFAULT 0');
            }
        }
    }

    public function down(): void
    {
        // revert to integer if desired
        if (Schema::hasColumn('medicine_batches', 'quantity')) {
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE medicine_batches MODIFY quantity INT NOT NULL DEFAULT 0');
            }
        }
    }
};
