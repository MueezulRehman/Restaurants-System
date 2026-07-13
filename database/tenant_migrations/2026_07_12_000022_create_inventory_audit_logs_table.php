<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('inventory_audit_logs')) {
            Schema::create('inventory_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
                $table->string('item_type'); // 'medicine_batch', 'menu_item', 'variant'
                $table->unsignedBigInteger('item_id');
                $table->enum('action', ['created', 'updated', 'deleted', 'purchased', 'sold', 'adjusted', 'recalled']);
                $table->json('before_values')->nullable(); // Previous values
                $table->json('after_values')->nullable(); // New values
                $table->unsignedBigInteger('user_id')->nullable();
                $table->text('reason')->nullable();
                $table->timestamps();

                $table->index('restaurant_id');
                $table->index('user_id');
                $table->index('created_at');
                $table->index(['item_type', 'item_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_audit_logs');
    }
};
