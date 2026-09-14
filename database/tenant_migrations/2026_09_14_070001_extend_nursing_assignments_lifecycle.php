<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nursing_assignments', function (Blueprint $table): void {
            $table->dateTime('ended_at')->nullable()->after('assigned_at');
            $table->string('shift', 20)->default('morning')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('nursing_assignments', function (Blueprint $table): void {
            $table->dropColumn(['ended_at', 'shift']);
        });
    }
};
