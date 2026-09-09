<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stock_adjustments') && Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE stock_adjustments MODIFY quantity_before DECIMAL(14,3) NOT NULL');
            DB::statement('ALTER TABLE stock_adjustments MODIFY quantity_after DECIMAL(14,3) NOT NULL');
            DB::statement('ALTER TABLE stock_adjustments MODIFY change_quantity DECIMAL(14,3) NOT NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('stock_adjustments') && Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE stock_adjustments MODIFY quantity_before INT NOT NULL');
            DB::statement('ALTER TABLE stock_adjustments MODIFY quantity_after INT NOT NULL');
            DB::statement('ALTER TABLE stock_adjustments MODIFY change_quantity INT NOT NULL');
        }
    }
};
