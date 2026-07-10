<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['orders', 'sales', 'inventory', 'financial', 'staff', 'delivery']);
            $table->string('name', 255);
            $table->json('filters')->nullable(); // date_from, date_to, category_id, etc.
            $table->json('data_snapshot')->nullable(); // cached report data
            $table->datetime('generated_at')->nullable();
            $table->timestamps();

            $table->index('restaurant_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
