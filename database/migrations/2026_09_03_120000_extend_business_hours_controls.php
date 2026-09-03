<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Manager day controls: early close, extend hours, closed today, weekly hours.
 * Runs on CENTRAL restaurants table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            if (! Schema::hasColumn('restaurants', 'opening_hours')) {
                $table->json('opening_hours')->nullable();
            }
            if (! Schema::hasColumn('restaurants', 'is_closed_today')) {
                $table->boolean('is_closed_today')->default(false);
            }
            if (! Schema::hasColumn('restaurants', 'closed_message')) {
                $table->string('closed_message', 255)->nullable();
            }
            if (! Schema::hasColumn('restaurants', 'accept_orders_when_closed')) {
                $table->boolean('accept_orders_when_closed')->default(false);
            }
            // Close earlier than weekly schedule (datetime today)
            if (! Schema::hasColumn('restaurants', 'early_close_at')) {
                $table->timestamp('early_close_at')->nullable();
            }
            // Stay open later than weekly schedule (datetime today)
            if (! Schema::hasColumn('restaurants', 'extend_close_at')) {
                $table->timestamp('extend_close_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            foreach (['early_close_at', 'extend_close_at'] as $col) {
                if (Schema::hasColumn('restaurants', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
