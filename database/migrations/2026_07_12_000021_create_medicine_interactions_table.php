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
        Schema::create('medicine_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id_1')->constrained('medicines')->onDelete('cascade');
            $table->foreignId('medicine_id_2')->constrained('medicines')->onDelete('cascade');
            $table->enum('interaction_type', ['contraindicated', 'serious', 'moderate', 'mild'])->default('moderate');
            $table->text('interaction_description');
            $table->text('recommended_action')->nullable(); // What to do if this interaction is detected
            $table->string('source')->nullable(); // e.g., "FDA", "WHO"
            $table->timestamps();

            $table->index('medicine_id_1');
            $table->index('medicine_id_2');
            $table->unique(['medicine_id_1', 'medicine_id_2']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_interactions');
    }
};
