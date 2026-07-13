<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Distinguishes a POS-rung sale from a customer's own online
            // order — needed since both flows write to the same orders
            // table, and there was previously no way to tell them apart
            // or attribute a sale to the staff member who made it.
            $table->string('channel')->default('online')->after('order_type');
            $table->foreignId('cashier_id')->nullable()->after('channel')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cashier_id');
            $table->dropColumn('channel');
        });
    }
};
