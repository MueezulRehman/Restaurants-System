<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Allow Super Admin to choose which businesses appear on the public homepage.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            if (! Schema::hasColumn('restaurants', 'show_on_homepage')) {
                $table->boolean('show_on_homepage')->default(false)->after('status');
            }
            if (! Schema::hasColumn('restaurants', 'homepage_sort_order')) {
                $table->unsignedInteger('homepage_sort_order')->default(0)->after('show_on_homepage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            if (Schema::hasColumn('restaurants', 'homepage_sort_order')) {
                $table->dropColumn('homepage_sort_order');
            }
            if (Schema::hasColumn('restaurants', 'show_on_homepage')) {
                $table->dropColumn('show_on_homepage');
            }
        });
    }
};
