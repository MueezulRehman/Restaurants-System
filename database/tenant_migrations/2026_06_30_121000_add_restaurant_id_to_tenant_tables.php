<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('deals', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('cashbook', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('attendance', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('salaries', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('restaurant_id');
        });

        Schema::table('attendance', function (Blueprint $table) {
            $table->dropConstrainedForeignId('restaurant_id');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('restaurant_id');
        });

        Schema::table('cashbook', function (Blueprint $table) {
            $table->dropConstrainedForeignId('restaurant_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('restaurant_id');
        });

        Schema::table('deals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('restaurant_id');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('restaurant_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('restaurant_id');
        });
    }
};
