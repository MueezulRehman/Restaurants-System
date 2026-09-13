<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('medicines') && ! Schema::hasColumn('medicines', 'is_controlled')) {
            Schema::table('medicines', function (Blueprint $table): void {
                $table->boolean('is_controlled')->default(false)->after('requires_prescription');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('medicines') && Schema::hasColumn('medicines', 'is_controlled')) {
            Schema::table('medicines', function (Blueprint $table): void {
                $table->dropColumn('is_controlled');
            });
        }
    }
};
