<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('service_cases')) {
            Schema::create('service_cases', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('menu_item_id')->nullable()->constrained()->nullOnDelete();
                $table->string('case_type', 20);
                $table->string('serial_number')->nullable();
                $table->string('status', 30)->default('received');
                $table->string('title');
                $table->text('description')->nullable();
                $table->text('resolution')->nullable();
                $table->decimal('estimated_cost', 12, 2)->nullable();
                $table->date('received_at');
                $table->date('due_at')->nullable();
                $table->date('completed_at')->nullable();
                $table->foreignId('assigned_to')->nullable();
                $table->foreignId('created_by')->nullable();
                $table->timestamps();
            });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('service_cases');
    }
};
