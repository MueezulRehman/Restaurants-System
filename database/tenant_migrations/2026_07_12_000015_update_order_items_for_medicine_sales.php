<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'medicine_batch_id')) {
                $table->foreignId('medicine_batch_id')->nullable()->after('product_variant_id')
                    ->constrained('medicine_batches')
                    ->nullOnDelete();
            }
        });

        // Update enum to include 'medicine' - handle both MySQL and SQLite
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `order_items` MODIFY `item_type` ENUM('menu_item','deal','medicine') NOT NULL DEFAULT 'menu_item'");
        }
        // SQLite doesn't support ENUM, but the column type is just TEXT/VARCHAR so it works automatically
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `order_items` MODIFY `item_type` ENUM('menu_item','deal') NOT NULL DEFAULT 'menu_item'");
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'medicine_batch_id')) {
                $table->dropForeign(['medicine_batch_id']);
                $table->dropColumn('medicine_batch_id');
            }
        });
    }
};
