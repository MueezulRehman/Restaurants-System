<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table): void {
            $table->text('diagnosis')->nullable()->after('reason');
            $table->text('notes')->nullable()->after('diagnosis');
        });

        Schema::table('prescriptions', function (Blueprint $table): void {
            $table->foreignId('visit_id')->nullable()->after('restaurant_id')->constrained('visits')->nullOnDelete();
            $table->foreignId('patient_id')->nullable()->after('visit_id')->constrained('patients')->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->after('patient_id')->constrained('doctors')->nullOnDelete();
            $table->index(['restaurant_id', 'visit_id']);
        });

        Schema::table('customer_allergies', function (Blueprint $table): void {
            $table->foreignId('patient_id')->nullable()->after('customer_id')->constrained('patients')->nullOnDelete();
            $table->index('patient_id');
        });
    }

    public function down(): void
    {
        Schema::table('customer_allergies', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('patient_id');
        });
        Schema::table('prescriptions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('doctor_id');
            $table->dropConstrainedForeignId('patient_id');
            $table->dropConstrainedForeignId('visit_id');
        });
        Schema::table('visits', function (Blueprint $table): void {
            $table->dropColumn(['diagnosis', 'notes']);
        });
    }
};
