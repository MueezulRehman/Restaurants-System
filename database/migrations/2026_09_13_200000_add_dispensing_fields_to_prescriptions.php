<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('prescriptions')) {
            return;
        }

        Schema::table('prescriptions', function (Blueprint $table): void {
            if (! Schema::hasColumn('prescriptions', 'dispensed_by')) {
                $table->foreignId('dispensed_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('prescriptions', 'dispensed_at')) {
                $table->timestamp('dispensed_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('prescriptions')) {
            return;
        }

        Schema::table('prescriptions', function (Blueprint $table): void {
            if (Schema::hasColumn('prescriptions', 'dispensed_by')) {
                $table->dropForeign(['dispensed_by']);
                $table->dropColumn('dispensed_by');
            }
            if (Schema::hasColumn('prescriptions', 'dispensed_at')) {
                $table->dropColumn('dispensed_at');
            }
        });
    }
};
