<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_allergies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->string('allergy_name');
            $table->text('description')->nullable();
            $table->string('severity', 20)->default('moderate');
            $table->json('trigger_medicines')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['restaurant_id', 'patient_id']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_allergies');
    }
};
