<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Widening an enum column needs raw SQL on MySQL; SQLite stores enums
        // as plain strings so no change is needed there (used in some test envs).
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN order_type ENUM('dine_in', 'takeaway', 'delivery', 'online', 'pos') NOT NULL DEFAULT 'online'");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("UPDATE orders SET order_type = 'takeaway' WHERE order_type = 'pos'");
            DB::statement("ALTER TABLE orders MODIFY COLUMN order_type ENUM('dine_in', 'takeaway', 'delivery', 'online') NOT NULL DEFAULT 'online'");
        }
    }
};
