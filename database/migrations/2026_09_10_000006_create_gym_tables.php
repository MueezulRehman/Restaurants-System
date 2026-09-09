<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('gym_plans')) {
            Schema::create('gym_plans', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->string('name', 120);
                $table->unsignedInteger('duration_days');
                $table->decimal('price', 12, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['restaurant_id', 'name']);
            });
        }

        if (! Schema::hasTable('gym_memberships')) {
            Schema::create('gym_memberships', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->foreignId('gym_plan_id')->constrained('gym_plans')->cascadeOnDelete();
                $table->date('starts_at');
                $table->date('ends_at');
                $table->string('status', 20)->default('active');
                $table->decimal('amount_paid', 12, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['restaurant_id', 'ends_at']);
                $table->index(['restaurant_id', 'status']);
            });
        }

        if (! Schema::hasTable('gym_check_ins')) {
            Schema::create('gym_check_ins', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('gym_membership_id')->constrained('gym_memberships')->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->dateTime('checked_in_at');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['restaurant_id', 'checked_in_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gym_check_ins');
        Schema::dropIfExists('gym_memberships');
        Schema::dropIfExists('gym_plans');
    }
};
