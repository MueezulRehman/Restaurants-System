<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vital_signs')) {
            return;
        }

        Schema::create('vital_signs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hospital_admission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('recorded_at');
            $table->decimal('temperature', 4, 1)->nullable();
            $table->string('blood_pressure', 20)->nullable();
            $table->unsignedSmallInteger('pulse')->nullable();
            $table->unsignedSmallInteger('respiratory_rate')->nullable();
            $table->decimal('oxygen_saturation', 4, 1)->nullable();
            $table->decimal('weight', 6, 2)->nullable();
            $table->unsignedTinyInteger('pain_score')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['restaurant_id', 'hospital_admission_id', 'recorded_at'], 'vitals_admission_recorded_idx');
        });
    }

    public function down(): void { Schema::dropIfExists('vital_signs'); }
};
