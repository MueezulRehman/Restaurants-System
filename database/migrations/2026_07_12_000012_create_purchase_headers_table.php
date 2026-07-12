<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_headers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('supplier_name')->nullable();
            $table->string('invoice_no')->nullable()->index();
            $table->date('purchase_date')->nullable();
            $table->decimal('total', 12, 2)->default(0);
            $table->string('status')->default('received');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_headers');
    }
};
