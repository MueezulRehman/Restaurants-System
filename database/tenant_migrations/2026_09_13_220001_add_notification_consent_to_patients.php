<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('patients') || Schema::hasColumn('patients', 'notification_consent')) {
            return;
        }

        Schema::table('patients', function (Blueprint $table): void {
            $table->boolean('notification_consent')->default(false)->after('phone');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('patients') && Schema::hasColumn('patients', 'notification_consent')) {
            Schema::table('patients', function (Blueprint $table): void {
                $table->dropColumn('notification_consent');
            });
        }
    }
};
