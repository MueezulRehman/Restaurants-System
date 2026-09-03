<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Unit-based pricing + fractional quantities for Codeibex POS.
 * @author Mueez Ul Rehman
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            if (! Schema::hasColumn('menu_items', 'unit_type')) {
                $table->string('unit_type', 30)->default('piece')->after('price');
            }
            if (! Schema::hasColumn('menu_items', 'price_per_unit')) {
                $table->decimal('price_per_unit', 12, 2)->nullable()->after('unit_type');
            }
            if (! Schema::hasColumn('menu_items', 'allow_fractional_qty')) {
                $table->boolean('allow_fractional_qty')->default(false)->after('price_per_unit');
            }
        });

        // Allow fractional stock (e.g. 2.5 kg)
        if (Schema::hasColumn('menu_items', 'stock_quantity')) {
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE menu_items MODIFY stock_quantity DECIMAL(14,3) NOT NULL DEFAULT 0');
            }
        }

        if (Schema::hasColumn('order_items', 'quantity')) {
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE order_items MODIFY quantity DECIMAL(14,3) NOT NULL DEFAULT 1');
            }
        }
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
