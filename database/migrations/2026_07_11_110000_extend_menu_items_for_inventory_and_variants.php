<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            if (!Schema::hasColumn('menu_items', 'cost_price')) {
                $table->decimal('cost_price', 10, 2)->default(0)->after('price');
            }
            if (!Schema::hasColumn('menu_items', 'unit')) {
                $table->string('unit', 50)->nullable()->after('cost_price');
            }
            if (!Schema::hasColumn('menu_items', 'has_variants')) {
                $table->boolean('has_variants')->default(false)->after('allows_toppings');
            }
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            if (Schema::hasColumn('menu_items', 'has_variants')) {
                $table->dropColumn('has_variants');
            }
            if (Schema::hasColumn('menu_items', 'unit')) {
                $table->dropColumn('unit');
            }
            if (Schema::hasColumn('menu_items', 'cost_price')) {
                $table->dropColumn('cost_price');
            }
        });
    }
};
