<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('categories') && ! Schema::hasColumn('categories', 'pos_show_line_edit')) {
            Schema::table('categories', function (Blueprint $table): void {
                $table->boolean('pos_show_line_edit')->default(false)->after('is_active');
            });
        }

        if (Schema::hasTable('menu_items') && ! Schema::hasColumn('menu_items', 'pos_show_line_edit')) {
            Schema::table('menu_items', function (Blueprint $table): void {
                $table->boolean('pos_show_line_edit')->default(false)->after('allow_fractional_qty');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'pos_show_line_edit')) {
            Schema::table('categories', function (Blueprint $table): void {
                $table->dropColumn('pos_show_line_edit');
            });
        }

        if (Schema::hasTable('menu_items') && Schema::hasColumn('menu_items', 'pos_show_line_edit')) {
            Schema::table('menu_items', function (Blueprint $table): void {
                $table->dropColumn('pos_show_line_edit');
            });
        }
    }
};
