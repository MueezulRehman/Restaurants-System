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
        Schema::create('batch_recalls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('medicine_id')->constrained('medicines')->onDelete('cascade');
            $table->foreignId('medicine_batch_id')->constrained('medicine_batches')->onDelete('cascade');
            $table->string('recall_number')->unique();
            $table->enum('reason', ['expiry', 'quality', 'contamination', 'regulatory', 'damage', 'other'])->default('quality');
            $table->text('description');
            $table->date('recall_date');
            $table->integer('quantity_recalled')->default(0);
            $table->enum('status', ['issued', 'in_progress', 'completed', 'cancelled'])->default('issued');
            $table->text('action_taken')->nullable();
            $table->foreignId('issued_by')->constrained('users')->onDelete('restrict');
            $table->softDeletes();
            $table->timestamps();

            $table->index('restaurant_id');
            $table->index('medicine_id');
            $table->index('status');
            $table->index('recall_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_recalls');
    }
};
