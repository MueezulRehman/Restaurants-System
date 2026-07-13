<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('manager_feedbacks')) {
            Schema::create('manager_feedbacks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->enum('type', ['bug_report', 'feature_request', 'complaint', 'general'])->default('general');
                $table->string('title');
                $table->text('message');
                $table->enum('status', ['new', 'reviewing', 'resolved', 'closed'])->default('new');
                $table->text('admin_reply')->nullable();
                $table->timestamp('replied_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->index('restaurant_id');
                $table->index('user_id');
                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('manager_feedbacks');
    }
};
