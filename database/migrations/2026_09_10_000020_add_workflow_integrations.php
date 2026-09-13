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
                $table->foreignId('wholesale_price_list_id')->nullable()->after('customer_id')->constrained('wholesale_price_lists')->nullOnDelete();
                $table->foreignId('sales_representative_id')->nullable()->after('wholesale_price_list_id')->constrained('sales_representatives')->nullOnDelete();
            });
        }
        if (Schema::hasTable('appointments') && ! Schema::hasColumn('appointments', 'service_package_purchase_id')) {
            Schema::table('appointments', function (Blueprint $table): void {
                $table->foreignId('service_package_purchase_id')->nullable()->after('customer_id')->constrained('service_package_purchases')->nullOnDelete();
            });
        }
        if (! Schema::hasTable('gym_trainer_schedules')) {
            Schema::create('gym_trainer_schedules', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('gym_trainer_id')->constrained()->cascadeOnDelete();
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
        if (Schema::hasTable('appointments') && Schema::hasColumn('appointments', 'service_package_purchase_id')) Schema::table('appointments', fn(Blueprint $table) => $table->dropForeign(['service_package_purchase_id'])->dropColumn('service_package_purchase_id'));
        if (Schema::hasTable('orders')) Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'sales_representative_id')) $table->dropForeign(['sales_representative_id'])->dropColumn('sales_representative_id');
            if (Schema::hasColumn('orders', 'wholesale_price_list_id')) $table->dropForeign(['wholesale_price_list_id'])->dropColumn('wholesale_price_list_id');
        });
    }
};
