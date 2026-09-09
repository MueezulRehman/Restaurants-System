<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            if (! Schema::hasColumn('restaurants', 'storefront_notice')) {
                $table->string('storefront_notice', 255)->nullable()->after('theme');
            }

            if (! Schema::hasColumn('restaurants', 'storefront_notice_enabled')) {
                $table->boolean('storefront_notice_enabled')->default(false)->after('storefront_notice');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            foreach (['storefront_notice', 'storefront_notice_enabled'] as $column) {
                if (Schema::hasColumn('restaurants', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
