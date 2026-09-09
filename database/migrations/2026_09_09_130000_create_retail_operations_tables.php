<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('retail_brands')) Schema::create('retail_brands', function (Blueprint $t) {
            $t->id();
            $t->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('description')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        if (! Schema::hasTable('retail_collections')) Schema::create('retail_collections', function (Blueprint $t) {
            $t->id();
            $t->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('season')->nullable();
            $t->date('starts_at')->nullable();
            $t->date('ends_at')->nullable();
            $t->timestamps();
        });
        if (! Schema::hasTable('loyalty_accounts')) Schema::create('loyalty_accounts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $t->integer('points')->default(0);
            $t->timestamps();
            $t->unique(['restaurant_id', 'customer_id']);
        });
        if (! Schema::hasTable('stock_transfers')) Schema::create('stock_transfers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $t->string('from_location');
            $t->string('to_location');
            $t->string('item_name');
            $t->decimal('quantity', 14, 3);
            $t->string('status')->default('completed');
            $t->foreignId('created_by')->nullable();
            $t->timestamps();
        });
        if (! Schema::hasTable('trade_ins')) Schema::create('trade_ins', function (Blueprint $t) {
            $t->id();
            $t->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $t->string('item_name');
            $t->string('serial_number')->nullable();
            $t->decimal('estimated_value', 12, 2);
            $t->string('condition')->nullable();
            $t->string('status')->default('received');
            $t->timestamps();
        });
        if (! Schema::hasTable('installment_plans')) Schema::create('installment_plans', function (Blueprint $t) {
            $t->id();
            $t->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $t->string('item_name');
            $t->decimal('total_amount', 12, 2);
            $t->decimal('deposit', 12, 2)->default(0);
            $t->integer('months');
            $t->string('status')->default('active');
            $t->date('next_due_at')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void
    {
        foreach (['installment_plans', 'trade_ins', 'stock_transfers', 'loyalty_accounts', 'retail_collections', 'retail_brands'] as $table) Schema::dropIfExists($table);
    }
};
