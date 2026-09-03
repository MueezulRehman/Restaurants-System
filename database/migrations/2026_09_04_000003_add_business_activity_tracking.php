<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'last_login_at')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->timestamp('last_login_at')->nullable()->after('remember_token');
                $table->timestamp('last_logout_at')->nullable()->after('last_login_at');
            });
        }

        if (! Schema::hasColumn('restaurants', 'activated_at')) {
            Schema::table('restaurants', function (Blueprint $table): void {
                $table->timestamp('activated_at')->nullable()->after('status');
            });

            DB::table('restaurants')
                ->where('status', 'active')
                ->whereNull('activated_at')
                ->update(['activated_at' => DB::raw('COALESCE(updated_at, created_at)')]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'last_logout_at')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn(['last_login_at', 'last_logout_at']);
            });
        }

        if (Schema::hasColumn('restaurants', 'activated_at')) {
            Schema::table('restaurants', function (Blueprint $table): void {
                $table->dropColumn('activated_at');
            });
        }
    }
};
