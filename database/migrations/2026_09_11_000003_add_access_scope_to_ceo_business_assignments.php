<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('ceo_business_assignments', 'access_scope')) {
            Schema::table('ceo_business_assignments', function (Blueprint $table): void {
                $table->string('access_scope')->default('all_branches')->after('access_level');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ceo_business_assignments', 'access_scope')) {
            Schema::table('ceo_business_assignments', function (Blueprint $table): void {
                $table->dropColumn('access_scope');
            });
        }
    }
};
