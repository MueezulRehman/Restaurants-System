<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin', 'ceo', 'admin', 'cashier', 'kitchen', 'rider', 'staff', 'manager') DEFAULT 'admin'");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin', 'admin', 'cashier', 'kitchen', 'rider', 'staff', 'manager') DEFAULT 'admin'");
        }
    }
};
