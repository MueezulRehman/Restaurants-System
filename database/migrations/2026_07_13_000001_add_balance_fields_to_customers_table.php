<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'balance')) {
                $table->decimal('balance', 10, 2)->default(0.00)->after('default_address');
            }

            if (! Schema::hasColumn('customers', 'last_reminder_at')) {
                $table->timestamp('last_reminder_at')->nullable()->after('balance');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'last_reminder_at')) {
                $table->dropColumn('last_reminder_at');
            }

            if (Schema::hasColumn('customers', 'balance')) {
                $table->dropColumn('balance');
            }
        });
    }
};
