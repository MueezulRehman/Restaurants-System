<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('appointments')) {
            return;
        }

        Schema::table('appointments', function (Blueprint $table): void {
            $table->foreignId('patient_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            $table->index(['restaurant_id', 'patient_id']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('appointments') || ! Schema::hasColumn('appointments', 'patient_id')) {
            return;
        }

        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropForeign(['patient_id']);
            $table->dropIndex(['restaurant_id', 'patient_id']);
            $table->dropColumn('patient_id');
        });
    }
};
