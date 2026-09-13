<?php

namespace App\View\Composers;

use App\Models\PlatformSetting;
use Illuminate\View\View;

class CeoLayoutComposer
{
    public function compose(View $view): void
    {
        $view->with([
            'platformName' => PlatformSetting::getValue('platform_name', 'CodeIbex'),
            'light' => PlatformSetting::getValue('platform_theme_light', '#E7F0FA'),
            'accent' => PlatformSetting::getValue('platform_theme_accent', '#7BA4D0'),
            'primary' => PlatformSetting::getValue('platform_theme_primary', '#2E5E99'),
            'dark' => PlatformSetting::getValue('platform_theme_dark', '#0D2440'),
            'route' => request()->route()?->getName() ?? '',
            'active' => fn (string $prefix): bool => str_starts_with(request()->route()?->getName() ?? '', $prefix),
        ]);
    }
}
