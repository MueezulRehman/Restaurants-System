<?php

use App\Models\RestaurantTheme;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        RestaurantTheme::query()->each(function (RestaurantTheme $theme): void {
            $palette = is_array($theme->manager_dark) ? $theme->manager_dark : [];
            $legacyDefault = [
                'background' => '#0F172A',
                'primary' => '#0F766E',
                'accent' => '#5EEAD4',
                'dark' => '#042F2E',
            ];

            if ($palette === $legacyDefault) {
                $theme->update(['manager_dark' => RestaurantTheme::defaults()['manager_dark']]);
            }
        });
    }

    public function down(): void
    {
        // Keep migrated CodeIbex palettes intact on rollback.
    }
};
