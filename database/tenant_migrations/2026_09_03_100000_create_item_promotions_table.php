<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('item_promotions')) {
            Schema::create('item_promotions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('restaurant_id')->index();
                $table->unsignedBigInteger('menu_item_id')->index();
                $table->string('label')->nullable();
                $table->enum('type', ['percent', 'fixed'])->default('percent');
                $table->decimal('value', 12, 2);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('item_promotions');
    }
};
