<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashbook', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['in', 'out']);
            $table->decimal('amount', 10, 2);
            $table->string('description', 255);
            $table->enum('source', ['order', 'manual', 'expense'])->default('manual');
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashbook');
    }
};
