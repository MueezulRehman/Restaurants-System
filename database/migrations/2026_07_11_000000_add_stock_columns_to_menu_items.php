<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('menu_items')) {
            return;
        }

        Schema::table('menu_items', function (Blueprint $table) {
            if (!Schema::hasColumn('menu_items', 'track_stock')) {
                $table->boolean('track_stock')->default(false)->after('is_available');
            }

            if (!Schema::hasColumn('menu_items', 'stock_quantity')) {
                $table->integer('stock_quantity')->default(0)->after('track_stock');
            }

            if (!Schema::hasColumn('menu_items', 'low_stock_threshold')) {
                $table->integer('low_stock_threshold')->default(0)->after('stock_quantity');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('menu_items')) {
            return;
        }

        Schema::table('menu_items', function (Blueprint $table) {
            if (Schema::hasColumn('menu_items', 'low_stock_threshold')) {
                $table->dropColumn('low_stock_threshold');
            }

            if (Schema::hasColumn('menu_items', 'stock_quantity')) {
                $table->dropColumn('stock_quantity');
            }

            if (Schema::hasColumn('menu_items', 'track_stock')) {
                $table->dropColumn('track_stock');
            }
        });
    }
};
