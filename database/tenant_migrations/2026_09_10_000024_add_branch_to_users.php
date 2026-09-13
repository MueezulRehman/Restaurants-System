<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'branch_id')) Schema::table('users', fn(Blueprint $table) => $table->unsignedBigInteger('branch_id')->nullable()->index());
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'branch_id')) Schema::table('users', fn(Blueprint $table) => $table->dropColumn('branch_id'));
    }
};
