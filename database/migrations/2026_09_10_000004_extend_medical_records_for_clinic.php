<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('medical_records', 'customer_id')) {
            Schema::table('medical_records', function (Blueprint $table): void {
                $table->foreignId('customer_id')->nullable()->after('restaurant_id')->constrained('customers')->nullOnDelete();
                $table->foreignId('appointment_id')->nullable()->after('customer_id')->constrained('appointments')->nullOnDelete();
                $table->string('doctor_name')->nullable()->after('medicine_name');
                $table->text('diagnosis')->nullable()->after('doctor_name');
                $table->dateTime('follow_up_at')->nullable()->after('diagnosis');
                $table->index(['restaurant_id', 'follow_up_at']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('medical_records', 'customer_id')) {
            Schema::table('medical_records', function (Blueprint $table): void {
                $table->dropForeign(['customer_id']);
                $table->dropForeign(['appointment_id']);
                $table->dropColumn(['customer_id', 'appointment_id', 'doctor_name', 'diagnosis', 'follow_up_at']);
            });
        }
    }
};
