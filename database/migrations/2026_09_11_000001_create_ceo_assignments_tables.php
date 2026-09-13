<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ceo_business_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('restaurant_id')->constrained('restaurants')->cascadeOnDelete();
            $table->string('access_level')->default('executive');
            $table->string('access_scope')->default('all_branches');
            $table->boolean('can_view_financials')->default(true);
            $table->boolean('can_view_staff')->default(true);
            $table->boolean('can_view_inventory')->default(true);
            $table->boolean('can_manage_branches')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'restaurant_id']);
        });

        Schema::create('ceo_branch_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('restaurant_id')->constrained('restaurants')->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id');
            $table->string('access_level')->default('executive');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'branch_id']);
            $table->index(['restaurant_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ceo_branch_assignments');
        Schema::dropIfExists('ceo_business_assignments');
    }
};
