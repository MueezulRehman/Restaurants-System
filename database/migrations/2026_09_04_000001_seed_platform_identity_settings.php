<?php

use App\Models\PlatformSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (
            [
                'platform_name' => 'CodeIbex',
                'platform_tagline' => 'Business platform',
                'platform_email' => '',
                'platform_phone' => '',
                'platform_address' => '',
                'platform_logo_path' => '',
                'platform_theme_light' => '#E7F0FA',
                'platform_theme_accent' => '#7BA4D0',
                'platform_theme_primary' => '#2E5E99',
                'platform_theme_dark' => '#0D2440',
            ] as $key => $value
        ) {
            PlatformSetting::setValue($key, $value);
        }
    }

    public function down(): void
    {
        PlatformSetting::query()->whereIn('key', [
            'platform_name',
            'platform_tagline',
            'platform_email',
            'platform_phone',
            'platform_address',
            'platform_logo_path',
        ])->delete();
    }
};
