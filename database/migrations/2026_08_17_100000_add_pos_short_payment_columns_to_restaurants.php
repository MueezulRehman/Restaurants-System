<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('restaurants', 'pos_allow_short_payment_without_debt')) {
            Schema::table('restaurants', function (Blueprint $table) {
                $table->boolean('pos_allow_short_payment_without_debt')->default(true)->after('enabled_modules');
                $table->integer('pos_short_payment_threshold')->default(10)->after('pos_allow_short_payment_without_debt');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('restaurants', 'pos_allow_short_payment_without_debt')) {
            Schema::table('restaurants', function (Blueprint $table) {
                $table->dropColumn(['pos_allow_short_payment_without_debt', 'pos_short_payment_threshold']);
            });
        }
    }
};
