<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $legacyId = DB::table('business_types')->where('name', 'General Business')->value('id');
        $canonicalId = DB::table('business_types')->where('name', 'General Store')->value('id');

        if ($legacyId && $canonicalId) {
            DB::table('restaurants')
                ->where('business_type_id', $legacyId)
                ->update(['business_type_id' => $canonicalId, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        $legacyId = DB::table('business_types')->where('name', 'General Business')->value('id');
        $canonicalId = DB::table('business_types')->where('name', 'General Store')->value('id');

        if ($legacyId && $canonicalId) {
            DB::table('restaurants')
                ->where('business_type_id', $canonicalId)
                ->update(['business_type_id' => $legacyId, 'updated_at' => now()]);
        }
    }
};
