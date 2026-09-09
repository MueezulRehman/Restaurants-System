<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table): void {
            if (! Schema::hasColumn('restaurants', 'storefront_enabled')) $table->boolean('storefront_enabled')->default(true)->after('id');
            if (! Schema::hasColumn('restaurants', 'storefront_module_override')) $table->boolean('storefront_module_override')->default(false)->after('storefront_enabled');
        });
    }
    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table): void {
            if (Schema::hasColumn('restaurants', 'storefront_module_override')) $table->dropColumn('storefront_module_override');
            if (Schema::hasColumn('restaurants', 'storefront_enabled')) $table->dropColumn('storefront_enabled');
        });
    }
};
