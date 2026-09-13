<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('wholesale_price_lists')) {
            Schema::create('wholesale_price_lists', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->string('name', 120);
                $table->string('customer_group', 80)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['restaurant_id', 'name']);
            });
        }
        if (! Schema::hasTable('wholesale_price_list_items')) {
            Schema::create('wholesale_price_list_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('wholesale_price_list_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('menu_item_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('price', 12, 2);
                $table->timestamps();
                $table->unique(['wholesale_price_list_id', 'product_variant_id', 'menu_item_id'], 'wholesale_price_list_item_unique');
            });
        }
        if (! Schema::hasTable('sales_representatives')) {
            Schema::create('sales_representatives', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('name', 150);
                $table->string('phone', 40)->nullable();
                $table->decimal('commission_rate', 8, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
        if (! Schema::hasColumn('customers', 'sales_representative_id')) {
            Schema::table('customers', function (Blueprint $table): void {
                $table->foreignId('sales_representative_id')->nullable()->after('restaurant_id')->constrained('sales_representatives')->nullOnDelete();
            });
        }
        if (! Schema::hasTable('service_packages')) {
            Schema::create('service_packages', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->unsignedInteger('included_visits')->default(1);
                $table->unsignedInteger('validity_days')->default(30);
                $table->decimal('price', 12, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('service_package_purchases')) {
            Schema::create('service_package_purchases', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('service_package_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->date('starts_at');
                $table->date('ends_at');
                $table->unsignedInteger('remaining_visits');
                $table->decimal('amount_paid', 12, 2)->default(0);
                $table->string('status', 20)->default('active');
                $table->timestamps();
                $table->index(['restaurant_id', 'status', 'ends_at']);
            });
        }
        if (! Schema::hasTable('gym_trainers')) {
            Schema::create('gym_trainers', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('name', 150);
                $table->string('specialty', 150)->nullable();
                $table->string('phone', 40)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
        if (! Schema::hasColumn('gym_memberships', 'gym_trainer_id')) {
            Schema::table('gym_memberships', function (Blueprint $table): void {
                $table->foreignId('gym_trainer_id')->nullable()->after('gym_plan_id')->constrained('gym_trainers')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('gym_memberships', 'gym_trainer_id')) Schema::table('gym_memberships', fn(Blueprint $table) => $table->dropForeign(['gym_trainer_id'])->dropColumn('gym_trainer_id'));
        Schema::dropIfExists('gym_trainers');
        Schema::dropIfExists('service_package_purchases');
        Schema::dropIfExists('service_packages');
        if (Schema::hasColumn('customers', 'sales_representative_id')) Schema::table('customers', fn(Blueprint $table) => $table->dropForeign(['sales_representative_id'])->dropColumn('sales_representative_id'));
        Schema::dropIfExists('sales_representatives');
        Schema::dropIfExists('wholesale_price_list_items');
        Schema::dropIfExists('wholesale_price_lists');
    }
};
