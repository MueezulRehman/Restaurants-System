<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'payment_status')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->string('payment_status', 20)->default('paid')->after('payment_method');
                $table->string('payment_reference', 150)->nullable()->after('payment_status');
                $table->index(['restaurant_id', 'payment_status']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'payment_status')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropIndex(['restaurant_id', 'payment_status']);
                $table->dropColumn(['payment_status', 'payment_reference']);
            });
        }
    }
};
