<?php

use App\Models\RestaurantTheme;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        RestaurantTheme::query()->each(function (RestaurantTheme $theme): void {
            $light = is_array($theme->manager_light) ? $theme->manager_light : [];
            $dark = is_array($theme->manager_dark) ? $theme->manager_dark : [];
            $changed = false;

            if (empty($light['surface']) && ! empty($light['background'])) {
                $light['surface'] = $light['background'];
                $changed = true;
            }
            if (empty($dark['surface']) && ! empty($dark['background'])) {
                $dark['surface'] = $dark['background'];
                $changed = true;
            }

            if ($changed) {
                $theme->update(['manager_light' => $light, 'manager_dark' => $dark]);
            }
        });
    }

    public function down(): void
    {
        // Keep backfilled surface values on rollback.
    }
};
