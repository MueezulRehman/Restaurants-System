<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hospital_admissions', function (Blueprint $table): void {
            $table->foreignId('bed_id')->nullable()->after('department_id')->constrained()->nullOnDelete();
            $table->index(['restaurant_id', 'bed_id']);
        });
    }

    public function down(): void
    {
        Schema::table('hospital_admissions', function (Blueprint $table): void {
            $table->dropForeign(['bed_id']);
            $table->dropIndex(['restaurant_id', 'bed_id']);
            $table->dropColumn('bed_id');
        });
    }
};
