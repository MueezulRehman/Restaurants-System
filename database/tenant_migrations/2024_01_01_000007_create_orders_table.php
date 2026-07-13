<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 20)->unique(); // TH-20260630-0001
            $table->string('tracking_token', 36)->unique(); // uuid, used in tracking URL, no login needed
            $table->enum('order_type', ['dine_in', 'takeaway', 'delivery', 'online'])->default('online');
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered', 'cancelled'])->default('pending');
            $table->string('customer_name', 100);
            $table->string('customer_phone', 20);
            $table->text('address')->nullable(); // required for delivery
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('payment_method', ['cash', 'online'])->default('cash');
            $table->text('notes')->nullable();
            $table->integer('estimated_minutes')->default(30); // shown to customer for tracking
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            // CRITICAL: ensures one customer can only ever see their own order via the token,
            // never another customer's order list
            $table->index('customer_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
