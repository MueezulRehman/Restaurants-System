<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('business_types')
            ->where('name', 'General Business')
            ->update(['is_active' => false, 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('business_types')
            ->where('name', 'General Business')
            ->update(['is_active' => true, 'updated_at' => now()]);
    }
};
