<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('medicines')) {
            Schema::table('medicines', function (Blueprint $table) {
                if (! Schema::hasColumn('medicines', 'unit_type')) {
                    $table->string('unit_type')->nullable()->after('barcode');
                }
                if (! Schema::hasColumn('medicines', 'allow_fractional_qty')) {
                    $table->boolean('allow_fractional_qty')->default(false)->after('unit_type');
                }
                if (! Schema::hasColumn('medicines', 'price_per_unit')) {
                    $table->decimal('price_per_unit', 12, 2)->nullable()->after('allow_fractional_qty');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('medicines')) {
            Schema::table('medicines', function (Blueprint $table) {
                if (Schema::hasColumn('medicines', 'price_per_unit')) {
                    $table->dropColumn('price_per_unit');
                }
                if (Schema::hasColumn('medicines', 'allow_fractional_qty')) {
                    $table->dropColumn('allow_fractional_qty');
                }
                if (Schema::hasColumn('medicines', 'unit_type')) {
                    $table->dropColumn('unit_type');
                }
            });
        }
    }
};
