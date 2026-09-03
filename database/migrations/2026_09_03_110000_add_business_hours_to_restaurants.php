<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Business hours + temporary closed (central restaurants table).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            if (! Schema::hasColumn('restaurants', 'opening_hours')) {
                $table->json('opening_hours')->nullable()->after('theme');
            }
            if (! Schema::hasColumn('restaurants', 'is_closed_today')) {
                $table->boolean('is_closed_today')->default(false)->after('opening_hours');
            }
            if (! Schema::hasColumn('restaurants', 'closed_message')) {
                $table->string('closed_message', 255)->nullable()->after('is_closed_today');
            }
            if (! Schema::hasColumn('restaurants', 'accept_orders_when_closed')) {
                // false = hard block online orders outside hours / closed today
                $table->boolean('accept_orders_when_closed')->default(false)->after('closed_message');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            foreach (['opening_hours', 'is_closed_today', 'closed_message', 'accept_orders_when_closed'] as $col) {
                if (Schema::hasColumn('restaurants', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
