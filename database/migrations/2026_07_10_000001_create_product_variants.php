<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Product variants table
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 100)->unique(); // SKU: e.g., 'PIZZA-LARGE-PEPPERONI'
            $table->string('variant_name', 255); // Display name: e.g., 'Large Pepperoni'
            $table->decimal('price_override', 10, 2)->nullable(); // Override base price if different
            $table->integer('quantity_available')->default(0); // Stock tracking
            $table->boolean('is_available')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['menu_item_id', 'sku']);
            $table->index('restaurant_id');
        });

        // Variant attributes (dimensions like Color, Size, Storage)
        Schema::create('variant_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100); // e.g., 'Color', 'Storage Capacity'
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('restaurant_id');
            $table->index('menu_item_id');
        });

        // Variant attribute values (e.g., Red, Blue, Green for Color)
        Schema::create('variant_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_attribute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->string('value', 100); // e.g., 'Red', '256GB'
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('variant_attribute_id');
            $table->index('product_variant_id');
        });

        // Add product_variant_id to order_items to track which variant was ordered
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete()->after('menu_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['product_variant_id']);
            $table->dropColumn('product_variant_id');
        });

        Schema::dropIfExists('variant_attribute_values');
        Schema::dropIfExists('variant_attributes');
        Schema::dropIfExists('product_variants');
    }
};
