<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('delivery_zones')) {
            Schema::create('delivery_zones', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->string('name', 120);
                $table->string('area_pattern', 180);
                $table->decimal('fee', 12, 2)->default(0);
                $table->decimal('minimum_order', 12, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['restaurant_id', 'name']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_zones');
    }
};
