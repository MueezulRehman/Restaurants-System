<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('commission_rules')) {
            Schema::create('commission_rules', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
                $table->string('type', 20);
                $table->decimal('value', 12, 2);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['restaurant_id', 'staff_id']);
            });
        }

        if (! Schema::hasTable('commission_earnings')) {
            Schema::create('commission_earnings', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('appointment_id')->unique()->constrained('appointments')->cascadeOnDelete();
                $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('base_amount', 12, 2);
                $table->decimal('commission_amount', 12, 2);
                $table->string('status', 20)->default('pending');
                $table->dateTime('paid_at')->nullable();
                $table->timestamps();
                $table->index(['restaurant_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_earnings');
        Schema::dropIfExists('commission_rules');
    }
};
