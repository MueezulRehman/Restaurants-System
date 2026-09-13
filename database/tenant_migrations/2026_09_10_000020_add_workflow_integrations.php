<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'wholesale_price_list_id')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->unsignedBigInteger('wholesale_price_list_id')->nullable()->index();
                $table->unsignedBigInteger('sales_representative_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('appointments') && ! Schema::hasColumn('appointments', 'service_package_purchase_id')) Schema::table('appointments', fn(Blueprint $table) => $table->unsignedBigInteger('service_package_purchase_id')->nullable()->index());
        if (! Schema::hasTable('gym_trainer_schedules')) {
            Schema::create('gym_trainer_schedules', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('restaurant_id')->index();
                $table->unsignedBigInteger('gym_trainer_id')->index();
                $table->unsignedTinyInteger('day_of_week');
                $table->time('starts_at');
                $table->time('ends_at');
                $table->unsignedInteger('capacity')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index(['restaurant_id', 'gym_trainer_id', 'day_of_week'], 'trainer_day_lookup_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gym_trainer_schedules');
        if (Schema::hasTable('appointments') && Schema::hasColumn('appointments', 'service_package_purchase_id')) Schema::table('appointments', fn(Blueprint $table) => $table->dropColumn('service_package_purchase_id'));
        if (Schema::hasTable('orders')) Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'sales_representative_id')) $table->dropColumn('sales_representative_id');
            if (Schema::hasColumn('orders', 'wholesale_price_list_id')) $table->dropColumn('wholesale_price_list_id');
        });
    }
};
