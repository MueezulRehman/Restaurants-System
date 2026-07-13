<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->string('prescription_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->string('patient_name');
            $table->string('doctor_name')->nullable();
            $table->date('prescription_date');
            $table->date('valid_until')->nullable();
            $table->text('medicines')->nullable(); // JSON: medicines details from prescription
            $table->string('image_path')->nullable(); // Prescription photo
            $table->enum('status', ['pending', 'verified', 'used', 'expired', 'rejected'])->default('pending');
            $table->text('verification_notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('restaurant_id');
            $table->index('customer_id');
            $table->index('order_id');
            $table->index('status');
            $table->index('prescription_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
