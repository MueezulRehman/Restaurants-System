<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $restaurants = DB::table('restaurants')->select('id', 'enabled_modules')->get();

        foreach ($restaurants as $restaurant) {
            if ($restaurant->enabled_modules === null) {
                continue;
            }

            $modules = json_decode($restaurant->enabled_modules, true);
            if (! is_array($modules) || in_array('storefront-notices', $modules, true)) {
                continue;
            }

            $modules[] = 'storefront-notices';
            DB::table('restaurants')->where('id', $restaurant->id)->update([
                'enabled_modules' => json_encode(array_values($modules)),
            ]);
        }
    }

    public function down(): void
    {
        $restaurants = DB::table('restaurants')->select('id', 'enabled_modules')->get();

        foreach ($restaurants as $restaurant) {
            $modules = json_decode($restaurant->enabled_modules ?? '[]', true);
            if (! is_array($modules)) {
                continue;
            }

            DB::table('restaurants')->where('id', $restaurant->id)->update([
                'enabled_modules' => json_encode(array_values(array_diff($modules, ['storefront-notices']))),
            ]);
        }
    }
};