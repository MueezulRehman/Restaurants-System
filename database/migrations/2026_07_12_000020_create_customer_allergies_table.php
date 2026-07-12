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
        Schema::create('customer_allergies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('allergy_name'); // e.g., "Penicillin", "Lactose"
            $table->text('description')->nullable();
            $table->enum('severity', ['mild', 'moderate', 'severe'])->default('moderate');
            $table->json('trigger_medicines')->nullable(); // JSON array of medicine IDs that trigger this
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('customer_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_allergies');
    }
};
