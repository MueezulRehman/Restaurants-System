<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Unit-based pricing for general store / retail / restaurant.
 * @author Mueez Ul Rehman
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            if (! Schema::hasColumn('menu_items', 'unit_type')) {
                $table->string('unit_type', 30)->default('piece')->after('price');
                // piece | kg | liter | dozen | custom
            }
            if (! Schema::hasColumn('menu_items', 'price_per_unit')) {
                $table->decimal('price_per_unit', 12, 2)->nullable()->after('unit_type');
            }
            if (! Schema::hasColumn('menu_items', 'allow_fractional_qty')) {
                $table->boolean('allow_fractional_qty')->default(false)->after('price_per_unit');
            }
        });

        // Mirror on tenant migrations if you use DB-per-tenant:
        // copy this file into database/tenant_migrations as well.
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            foreach (['allow_fractional_qty', 'price_per_unit', 'unit_type'] as $col) {
                if (Schema::hasColumn('menu_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
