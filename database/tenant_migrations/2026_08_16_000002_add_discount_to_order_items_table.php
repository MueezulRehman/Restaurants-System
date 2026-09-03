<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0)->after('unit_price');
            }
            if (!Schema::hasColumn('order_items', 'discount_type')) {
                $table->string('discount_type', 20)->default('fixed')->after('discount_amount');
            }
            if (!Schema::hasColumn('order_items', 'original_total')) {
                $table->decimal('original_total', 12, 2)->nullable()->after('discount_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'discount_type', 'original_total']);
        });
    }
};
