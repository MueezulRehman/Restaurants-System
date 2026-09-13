<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_notification_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('queue_entry_id');
            $table->string('channel', 20);
            $table->string('recipient_masked', 64);
            $table->string('provider_message_id')->nullable();
            $table->string('status', 20)->default('queued');
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['restaurant_id', 'queue_entry_id']);
            $table->index(['restaurant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_notification_deliveries');
    }
};
