<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            // base price - used directly when item has NO sizes (e.g. burgers, shawarma, rolls)
            $table->decimal('price', 10, 2)->nullable();
            $table->boolean('has_sizes')->default(false); // true for pizza, pasta, cheese stick, fries
            $table->string('image', 255)->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('allows_toppings')->default(false); // true for pizza
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
