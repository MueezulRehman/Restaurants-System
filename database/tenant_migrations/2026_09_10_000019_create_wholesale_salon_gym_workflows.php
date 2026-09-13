<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (
            [
                'wholesale_price_lists' => function (Blueprint $t): void {
                    $t->id();
                    $t->unsignedBigInteger('restaurant_id')->index();
                    $t->string('name', 120);
                    $t->string('customer_group', 80)->nullable();
                    $t->boolean('is_active')->default(true);
                    $t->timestamps();
                    $t->unique(['restaurant_id', 'name']);
                },
                'wholesale_price_list_items' => function (Blueprint $t): void {
                    $t->id();
                    $t->unsignedBigInteger('wholesale_price_list_id');
                    $t->unsignedBigInteger('product_variant_id')->nullable();
                    $t->unsignedBigInteger('menu_item_id')->nullable();
                    $t->decimal('price', 12, 2);
                    $t->timestamps();
                    $t->index('wholesale_price_list_id');
                },
                'sales_representatives' => function (Blueprint $t): void {
                    $t->id();
                    $t->unsignedBigInteger('restaurant_id')->index();
                    $t->unsignedBigInteger('user_id')->nullable();
                    $t->string('name', 150);
                    $t->string('phone', 40)->nullable();
                    $t->decimal('commission_rate', 8, 2)->default(0);
                    $t->boolean('is_active')->default(true);
                    $t->timestamps();
                },
                'service_packages' => function (Blueprint $t): void {
                    $t->id();
                    $t->unsignedBigInteger('restaurant_id')->index();
                    $t->string('name', 150);
                    $t->text('description')->nullable();
                    $t->unsignedInteger('included_visits')->default(1);
                    $t->unsignedInteger('validity_days')->default(30);
                    $t->decimal('price', 12, 2)->default(0);
                    $t->boolean('is_active')->default(true);
                    $t->timestamps();
                },
                'service_package_purchases' => function (Blueprint $t): void {
                    $t->id();
                    $t->unsignedBigInteger('restaurant_id')->index();
                    $t->unsignedBigInteger('service_package_id');
                    $t->unsignedBigInteger('customer_id');
                    $t->date('starts_at');
                    $t->date('ends_at');
                    $t->unsignedInteger('remaining_visits');
                    $t->decimal('amount_paid', 12, 2)->default(0);
                    $t->string('status', 20)->default('active');
                    $t->timestamps();
                    $t->index(['restaurant_id', 'status', 'ends_at']);
                },
                'gym_trainers' => function (Blueprint $t): void {
                    $t->id();
                    $t->unsignedBigInteger('restaurant_id')->index();
                    $t->unsignedBigInteger('user_id')->nullable();
                    $t->string('name', 150);
                    $t->string('specialty', 150)->nullable();
                    $t->string('phone', 40)->nullable();
                    $t->boolean('is_active')->default(true);
                    $t->timestamps();
                },
            ] as $name => $definition
        ) {
            if (! Schema::hasTable($name)) Schema::create($name, $definition);
        }
        if (Schema::hasTable('customers') && ! Schema::hasColumn('customers', 'sales_representative_id')) Schema::table('customers', fn(Blueprint $t) => $t->unsignedBigInteger('sales_representative_id')->nullable()->index());
        if (Schema::hasTable('gym_memberships') && ! Schema::hasColumn('gym_memberships', 'gym_trainer_id')) Schema::table('gym_memberships', fn(Blueprint $t) => $t->unsignedBigInteger('gym_trainer_id')->nullable()->index());
    }

    public function down(): void
    {
        if (Schema::hasTable('gym_memberships') && Schema::hasColumn('gym_memberships', 'gym_trainer_id')) Schema::table('gym_memberships', fn(Blueprint $t) => $t->dropColumn('gym_trainer_id'));
        Schema::dropIfExists('gym_trainers');
        Schema::dropIfExists('service_package_purchases');
        Schema::dropIfExists('service_packages');
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'sales_representative_id')) Schema::table('customers', fn(Blueprint $t) => $t->dropColumn('sales_representative_id'));
        Schema::dropIfExists('sales_representatives');
        Schema::dropIfExists('wholesale_price_list_items');
        Schema::dropIfExists('wholesale_price_lists');
    }
};
