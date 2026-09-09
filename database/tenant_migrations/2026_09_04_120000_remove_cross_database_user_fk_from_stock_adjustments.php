<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stock_adjustments') || ! Schema::hasColumn('stock_adjustments', 'user_id')) {
            return;
        }

        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('stock_adjustments') || ! Schema::hasColumn('stock_adjustments', 'user_id')) {
            return;
        }

        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
