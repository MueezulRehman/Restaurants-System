<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reason')->nullable();
            $table->string('status', 30)->default('checked_in');
            $table->dateTime('checked_in_at');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
            $table->index(['restaurant_id', 'status']);
        });

        Schema::create('queue_counters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->date('queue_date');
            $table->unsignedInteger('last_issued_number')->default(0);
            $table->timestamps();
            $table->unique(['restaurant_id', 'doctor_id', 'queue_date']);
        });

        Schema::create('queue_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visit_id')->unique()->constrained()->cascadeOnDelete();
            $table->date('queue_date');
            $table->unsignedInteger('token_number');
            $table->string('status', 30)->default('waiting');
            $table->dateTime('called_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('no_show_at')->nullable();
            $table->timestamps();
            $table->unique(['restaurant_id', 'doctor_id', 'queue_date', 'token_number'], 'queue_entries_token_unique');
            $table->index(['restaurant_id', 'doctor_id', 'queue_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_entries');
        Schema::dropIfExists('queue_counters');
        Schema::dropIfExists('visits');
    }
};
