<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('service_cases', 'parts_used')) {
            Schema::table('service_cases', function (Blueprint $table): void {
                $table->text('parts_used')->nullable()->after('resolution');
                $table->decimal('final_cost', 12, 2)->nullable()->after('estimated_cost');
                $table->dateTime('collection_notified_at')->nullable()->after('completed_at');
                $table->index(['restaurant_id', 'collection_notified_at']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('service_cases', 'parts_used')) {
            Schema::table('service_cases', function (Blueprint $table): void {
                $table->dropColumn(['parts_used', 'final_cost', 'collection_notified_at']);
            });
        }
    }
};
