<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('recipes')) {
            Schema::create('recipes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
                $table->string('name', 150);
                $table->decimal('yield_quantity', 14, 3)->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('recipe_ingredients')) {
            Schema::create('recipe_ingredients', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
                $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
                $table->decimal('quantity', 14, 3);
                $table->timestamps();
                $table->unique(['recipe_id', 'menu_item_id']);
            });
        }

        if (! Schema::hasTable('production_batches')) {
            Schema::create('production_batches', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
                $table->string('batch_number', 80);
                $table->decimal('quantity', 14, 3);
                $table->dateTime('produced_at');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->unique(['restaurant_id', 'batch_number']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('production_batches');
        Schema::dropIfExists('recipe_ingredients');
        Schema::dropIfExists('recipes');
    }
};
