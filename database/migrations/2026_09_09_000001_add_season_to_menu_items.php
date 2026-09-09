<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('menu_items') && ! Schema::hasColumn('menu_items', 'season')) {
            Schema::table('menu_items', function (Blueprint $table): void {
                $table->string('season', 20)->default('all-season')->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('menu_items') && Schema::hasColumn('menu_items', 'season')) {
            Schema::table('menu_items', function (Blueprint $table): void {
                $table->dropColumn('season');
            });
        }
    }
};
