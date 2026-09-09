<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inventory_purchases')) {
            Schema::create('inventory_purchases', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
                $table->string('supplier_name')->nullable();
                $table->string('invoice_no')->nullable();
                $table->date('purchase_date');
                $table->decimal('total', 12, 2)->default(0);
                $table->string('status')->default('received');
                $table->foreignId('created_by')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('inventory_purchase_items')) {
            Schema::create('inventory_purchase_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('inventory_purchase_id')->constrained()->cascadeOnDelete();
                $table->foreignId('menu_item_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('quantity', 14, 3);
                $table->decimal('purchase_price', 12, 2);
                $table->decimal('selling_price', 12, 2)->nullable();
                $table->decimal('line_total', 12, 2);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_purchase_items');
        Schema::dropIfExists('inventory_purchases');
    }
};
