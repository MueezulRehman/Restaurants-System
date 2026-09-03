<?php

use App\Models\PlatformSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (
            [
                'platform_theme_light' => '#E7F0FA',
                'platform_theme_accent' => '#7BA4D0',
                'platform_theme_primary' => '#2E5E99',
                'platform_theme_dark' => '#0D2440',
            ] as $key => $value
        ) {
            if (PlatformSetting::getValue($key) === null) {
                PlatformSetting::setValue($key, $value);
            }
        }
    }

    public function down(): void
    {
        PlatformSetting::query()->whereIn('key', [
            'platform_theme_light',
            'platform_theme_accent',
            'platform_theme_primary',
            'platform_theme_dark',
        ])->delete();
    }
};
