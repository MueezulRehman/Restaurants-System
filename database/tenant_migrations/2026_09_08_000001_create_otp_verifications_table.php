<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_verifications', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('restaurant_id');
            $table->string('phone', 20);
            $table->string('code_hash');
            $table->string('purpose', 50)->default('guest_order');
            $table->json('channels');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->index(['restaurant_id', 'phone', 'purpose']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};
