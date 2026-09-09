<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('follow_up_reminders')) {
            Schema::create('follow_up_reminders', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->foreignId('medical_record_id')->nullable()->constrained()->nullOnDelete();
                $table->dateTime('due_at');
                $table->text('note')->nullable();
                $table->string('status', 20)->default('scheduled');
                $table->dateTime('completed_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['restaurant_id', 'status', 'due_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_up_reminders');
    }
};
