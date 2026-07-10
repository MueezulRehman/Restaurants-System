<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Subscription plans table
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 10, 2);
            $table->decimal('price_yearly', 10, 2)->nullable();
            $table->integer('trial_days')->default(14); // Free trial period
            $table->integer('max_staff')->default(10);
            $table->integer('max_menu_items')->default(100);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Subscription features table (e.g., "Analytics", "API Access", "Custom Domain")
        Schema::create('subscription_features', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Pivot table: features included in each plan
        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_feature_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Explicit short index name to avoid MySQL identifier length limits
            $table->unique(['subscription_plan_id', 'subscription_feature_id'], 'pf_plan_feat_unique');
        });

        // Restaurant subscriptions table
        Schema::create('restaurant_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->datetime('trial_ends_at')->nullable();
            $table->datetime('current_period_start')->nullable();
            $table->datetime('current_period_end')->nullable();
            $table->string('payment_method', 50)->nullable(); // stripe, jazzcash, easypysa, etc. (TODO)
            $table->enum('status', ['trial', 'active', 'cancelled', 'expired'])->default('trial');
            $table->boolean('auto_renew')->default(true);
            $table->timestamps();

            $table->index('restaurant_id');
            $table->index('status');
        });

        // Billing cycles table (invoice history)
        Schema::create('billing_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_subscription_id')->constrained()->cascadeOnDelete();
            $table->datetime('period_start');
            $table->datetime('period_end');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->datetime('paid_at')->nullable();
            $table->string('invoice_number', 100)->unique()->nullable();
            $table->timestamps();

            $table->index('restaurant_subscription_id');
            $table->index('status');
            $table->index('period_start');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_cycles');
        Schema::dropIfExists('restaurant_subscriptions');
        Schema::dropIfExists('plan_features');
        Schema::dropIfExists('subscription_features');
        Schema::dropIfExists('subscription_plans');
    }
};
