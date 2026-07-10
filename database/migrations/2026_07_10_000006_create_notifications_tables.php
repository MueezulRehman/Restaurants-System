<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Notifications table
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // For staff notifications
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete(); // For customer notifications
            $table->enum('type', ['order_update', 'feedback_reply', 'low_stock', 'delivery_update', 'custom'])->default('custom');
            $table->string('title', 255);
            $table->text('message');
            $table->json('channels'); // ['email', 'sms', 'push'] or single channel
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->datetime('sent_at')->nullable();
            $table->datetime('read_at')->nullable();
            $table->timestamps();

            $table->index('restaurant_id');
            $table->index('user_id');
            $table->index('customer_id');
            $table->index('type');
            $table->index('status');
        });

        // Notification preferences table (per user/customer)
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->morphs('notifiable'); // user_id or customer_id
            $table->boolean('email_enabled')->default(true);
            $table->boolean('push_enabled')->default(true);
            $table->boolean('whatsapp_enabled')->default(false);
            $table->boolean('low_stock_alerts')->default(true);
            $table->boolean('order_updates')->default(true);
            $table->boolean('feedback_replies')->default(true);
            $table->timestamps();

            $table->unique(['notifiable_type', 'notifiable_id']);
        });

        // Web push subscriptions (for browser push notifications)
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->morphs('subscriber'); // user_id or customer_id
            $table->string('endpoint', 2048); // Push service endpoint
            $table->json('keys'); // P256dh and auth keys
            $table->timestamps();

            $table->unique(['subscriber_type', 'subscriber_id', 'endpoint']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notifications');
    }
};
