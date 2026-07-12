<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'table_number')) {
                $table->string('table_number', 20)->nullable()->after('order_type');
            }
        });

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN order_type ENUM('dine_in','takeaway','delivery','online','table','pos') NOT NULL DEFAULT 'online'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN order_type ENUM('dine_in','takeaway','delivery','online','pos') NOT NULL DEFAULT 'online'");
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('table_number');
        });
    }
};
