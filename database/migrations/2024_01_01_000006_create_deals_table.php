<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // "Deal 8"
            $table->integer('deal_number');
            $table->decimal('price', 10, 2);
            $table->text('description'); // "2 Zinger Burger, 6 Hot Wings, 6 Nuggets, 1 Litre Drink, Fries"
            $table->string('image', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
