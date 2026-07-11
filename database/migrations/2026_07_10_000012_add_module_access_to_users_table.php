<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-user module access grants.
     *
     * Lets a restaurant admin decide, module by module, which parts of the
     * panel a specific manager account may use (e.g. a manager might be
     * granted "menu" + "categories" but not "cashbook" or "staff"). Admins
     * themselves are never restricted by this — it only applies to the
     * `manager` role. Null/empty means "no modules granted yet".
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'module_access')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('module_access')->nullable()->after('role');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('module_access');
        });
    }
};
