<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('inventory_purchase_items', 'expiry_date')) {
            Schema::table('inventory_purchase_items', function (Blueprint $table): void {
                $table->date('expiry_date')->nullable()->after('line_total');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('inventory_purchase_items', 'expiry_date')) {
            Schema::table('inventory_purchase_items', function (Blueprint $table): void {
                $table->dropColumn('expiry_date');
            });
        }
    }
};
