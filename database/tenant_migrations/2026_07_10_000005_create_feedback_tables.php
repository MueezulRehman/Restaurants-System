<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Feedback table
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete(); // Can be submitted by customer or staff
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Staff user submitting feedback
            $table->enum('type', ['suggestion', 'complaint', 'praise', 'bug_report'])->default('suggestion');
            $table->string('title', 255);
            $table->text('message');
            $table->integer('rating')->nullable()->comment('1-5 star rating');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->datetime('resolved_at')->nullable();
            $table->timestamps();

            $table->index('restaurant_id');
            $table->index('status');
            $table->index('created_at');
        });

        // Feedback replies table
        Schema::create('feedback_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Manager/admin replying
            $table->text('message');
            $table->boolean('is_internal')->default(false); // Internal notes (not shown to customer)
            $table->timestamps();

            $table->index('feedback_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_replies');
        Schema::dropIfExists('feedback');
    }
};
