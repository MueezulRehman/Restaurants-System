<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('restaurants', 'enabled_modules')) {
            Schema::table('restaurants', function (Blueprint $table) {
                $table->json('enabled_modules')->nullable()->after('business_type_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('enabled_modules');
        });
    }
};
