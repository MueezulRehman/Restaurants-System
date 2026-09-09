<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stock_adjustments')) {
            return;
        }

        Schema::table('stock_adjustments', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_adjustments', 'menu_item_id')) {
                $table->unsignedBigInteger('menu_item_id')->nullable()->after('product_variant_id');
                $table->index('menu_item_id');
            }

            if (! Schema::hasColumn('stock_adjustments', 'medicine_batch_id')) {
                $table->unsignedBigInteger('medicine_batch_id')->nullable()->after('menu_item_id');
                $table->index('medicine_batch_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('stock_adjustments')) {
            return;
        }

        Schema::table('stock_adjustments', function (Blueprint $table) {
            if (Schema::hasColumn('stock_adjustments', 'medicine_batch_id')) {
                $table->dropIndex(['medicine_batch_id']);
                $table->dropColumn('medicine_batch_id');
            }

            if (Schema::hasColumn('stock_adjustments', 'menu_item_id')) {
                $table->dropIndex(['menu_item_id']);
                $table->dropColumn('menu_item_id');
            }
        });
    }
};
