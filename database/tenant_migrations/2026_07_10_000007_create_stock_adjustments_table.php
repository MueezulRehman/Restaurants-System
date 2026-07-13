<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Who made the adjustment
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->integer('change_quantity'); // Can be positive or negative
            $table->enum('reason', [
                'sale', 'return', 'recount', 'damage', 'expiry',
                'purchase', 'adjustment', 'correction', 'other'
            ])->default('adjustment');
            $table->string('reference_id', 100)->nullable(); // Order ID, Purchase ID, etc.
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('restaurant_id');
            $table->index('product_variant_id');
            $table->index('user_id');
            $table->index('reason');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
