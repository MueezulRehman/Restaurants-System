<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stock_adjustments')) {
            Schema::create('stock_adjustments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
                $table->enum('adjustment_type', ['in', 'out', 'correction'])->default('correction');
                $table->integer('quantity_before');
                $table->integer('quantity_after');
                $table->integer('quantity_changed');
                $table->string('reason')->nullable();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->index('restaurant_id');
                $table->index('menu_item_id');
                $table->index('adjustment_type');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
