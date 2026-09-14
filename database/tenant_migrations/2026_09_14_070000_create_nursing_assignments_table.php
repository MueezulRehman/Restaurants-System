<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nursing_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hospital_admission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('nurse_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('assigned_at');
            $table->string('status', 30)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['restaurant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nursing_assignments');
    }
};
