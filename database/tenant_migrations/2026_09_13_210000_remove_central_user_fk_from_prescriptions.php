<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('prescriptions') || ! Schema::hasColumn('prescriptions', 'dispensed_by')) {
            return;
        }

        $foreignKeyExists = collect(Schema::getForeignKeys('prescriptions'))
            ->contains(fn (array $foreignKey): bool => ($foreignKey['name'] ?? null) === 'prescriptions_dispensed_by_foreign');

        if ($foreignKeyExists) {
            Schema::table('prescriptions', function (Blueprint $table): void {
                $table->dropForeign(['dispensed_by']);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('prescriptions') || ! Schema::hasColumn('prescriptions', 'dispensed_by')) {
            return;
        }

        Schema::table('prescriptions', function (Blueprint $table): void {
            $table->foreign('dispensed_by')->references('id')->on('users')->nullOnDelete();
        });
    }
};
