<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            // Barcode / medicine code / SKU — used by the Retail and Medical Store
            // POS screens to look items up by scan or by typed code.
            $table->string('sku', 100)->nullable()->after('name');
            $table->boolean('track_stock')->default(false)->after('is_available');
            $table->integer('stock_quantity')->default(0)->after('track_stock');
            $table->integer('low_stock_threshold')->default(5)->after('stock_quantity');

            $table->index(['restaurant_id', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropIndex(['restaurant_id', 'sku']);
            $table->dropColumn(['sku', 'track_stock', 'stock_quantity', 'low_stock_threshold']);
        });
    }
};
