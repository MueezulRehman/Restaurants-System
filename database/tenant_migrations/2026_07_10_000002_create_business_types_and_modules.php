<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Business types table (Restaurant, Cafe, Bakery, etc.)
        Schema::create('business_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable(); // e.g., 'utensils', 'coffee', 'cake'
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Modules table (Orders, POS, Delivery, Reports, Feedback, Notifications, etc.)
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('key', 50)->unique(); // kebab-case: 'orders', 'pos', 'delivery', 'reports', etc.
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Pivot table: which modules are enabled for which business types
        Schema::create('business_type_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['business_type_id', 'module_id']);
        });

        // Add business_type_id to restaurants table
        Schema::table('restaurants', function (Blueprint $table) {
            $table->foreignId('business_type_id')->nullable()->constrained()->nullOnDelete()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['business_type_id']);
            $table->dropColumn('business_type_id');
        });

        Schema::dropIfExists('business_type_modules');
        Schema::dropIfExists('modules');
        Schema::dropIfExists('business_types');
    }
};
