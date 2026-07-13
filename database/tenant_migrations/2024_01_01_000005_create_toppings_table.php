<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toppings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // "Extra Topping (S)", "Dip Sauce", "Cheese Slice"
            $table->decimal('price', 10, 2);
            $table->enum('type', ['topping', 'sauce', 'extra'])->default('topping');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toppings');
    }
};
