<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'branch_id')) {
            Schema::table('orders', fn(Blueprint $table) => $table->unsignedBigInteger('branch_id')->nullable()->index());
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'branch_id')) Schema::table('orders', fn(Blueprint $table) => $table->dropColumn('branch_id'));
    }
};
