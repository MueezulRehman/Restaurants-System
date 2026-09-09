<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->string('code', 50);
                $table->string('type', 20);
                $table->decimal('value', 12, 2);
                $table->decimal('minimum_order', 12, 2)->default(0);
                $table->decimal('max_discount', 12, 2)->nullable();
                $table->dateTime('starts_at')->nullable();
                $table->dateTime('ends_at')->nullable();
                $table->unsignedInteger('usage_limit')->nullable();
                $table->unsignedInteger('usage_count')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['restaurant_id', 'code']);
            });
        }

        if (! Schema::hasColumn('orders', 'coupon_code')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->string('coupon_code', 50)->nullable()->after('discount_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'coupon_code')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropColumn('coupon_code');
            });
        }
        Schema::dropIfExists('coupons');
    }
};
