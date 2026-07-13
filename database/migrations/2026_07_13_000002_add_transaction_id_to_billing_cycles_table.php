<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_cycles', function (Blueprint $table) {
            if (! Schema::hasColumn('billing_cycles', 'transaction_id')) {
                $table->string('transaction_id', 255)->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('billing_cycles', function (Blueprint $table) {
            if (Schema::hasColumn('billing_cycles', 'transaction_id')) {
                $table->dropColumn('transaction_id');
            }
        });
    }
};
