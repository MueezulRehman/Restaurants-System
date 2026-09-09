<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_devices')) {
            Schema::create('product_devices', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('menu_item_id')->nullable()->constrained('menu_items')->nullOnDelete();
                $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
                $table->string('identifier_type', 20)->default('serial');
                $table->string('identifier_value', 150);
                $table->date('purchase_date')->nullable();
                $table->date('warranty_until')->nullable();
                $table->string('status', 30)->default('in_stock');
                $table->timestamps();
                $table->unique(['restaurant_id', 'identifier_value']);
                $table->index(['restaurant_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_devices');
    }
};
