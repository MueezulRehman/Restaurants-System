<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'branch_id')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->foreignId('branch_id')->nullable()->after('restaurant_id')->constrained('branches')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'branch_id')) {
            Schema::table('orders', fn(Blueprint $table) => $table->dropForeign(['branch_id'])->dropColumn('branch_id'));
        }
    }
};
