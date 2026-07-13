<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('custom_domain')->nullable()->unique();
            $table->string('plan')->default('basic');
            $table->enum('status', ['trial', 'active', 'suspended', 'cancelled'])->default('trial');
            $table->string('logo_path')->nullable();
            $table->text('theme')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin','admin','cashier','kitchen','rider','staff','manager') DEFAULT 'admin'");
        }

        DB::table('users')->where('role', 'admin')->update(['role' => 'super_admin']);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'super_admin')->whereNotNull('restaurant_id')->update(['role' => 'admin']);

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('restaurant_id');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','cashier','kitchen','rider','staff','manager') DEFAULT 'admin'");
        }

        Schema::dropIfExists('restaurants');
    }
};
