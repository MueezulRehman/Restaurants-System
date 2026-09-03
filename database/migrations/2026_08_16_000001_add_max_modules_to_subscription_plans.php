<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds a per-plan module cap. Null = unlimited modules (e.g. a
     * "Premium" tier). Used to limit how many modules a super admin can
     * enable for a business on a given subscription plan.
     */
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->unsignedInteger('max_modules')->nullable()->after('max_menu_items');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('max_modules');
        });
    }
};
