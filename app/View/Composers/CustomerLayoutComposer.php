<?php

namespace App\View\Composers;

use App\Models\PlatformSetting;
use Illuminate\View\View;

class CustomerLayoutComposer
{
    public function compose(View $view): void
    {
        $restaurant = request()->routeIs('home')
            ? null
            : (app()->bound('restaurant') ? app('restaurant') : null);
        $platformName = PlatformSetting::getValue('platform_name', 'CodeIbex');
        $platformTagline = PlatformSetting::getValue('platform_tagline', 'Business platform');
        $defaultTitle = $restaurant
            ? $restaurant->name . ($restaurant->tagline ? ' — ' . $restaurant->tagline : '')
            : $platformName;
        $defaultDescription = $restaurant
            ? 'Explore ' . $restaurant->name . ' on ' . $platformName . '.'
            : ($platformTagline ?: 'Business operations and customer experiences on one platform.');
        $platformLight = PlatformSetting::getValue('platform_theme_light', '#E7F0FA');
        $platformAccent = PlatformSetting::getValue('platform_theme_accent', '#7BA4D0');
        $platformPrimary = PlatformSetting::getValue('platform_theme_primary', '#2E5E99');
        $platformDark = PlatformSetting::getValue('platform_theme_dark', '#0D2440');

        $view->with([
            'r' => $restaurant,
            'useModernMenuHeader' => $restaurant
                && request()->routeIs('menu.restaurant')
                && $restaurant->getCustomerMenuTemplate() === 'modern',
            'platformName' => $platformName,
            'platformTagline' => $platformTagline,
            'pageTitle' => trim($view->getFactory()->yieldContent('title', $defaultTitle)),
            'pageDescription' => trim($view->getFactory()->yieldContent('description', $defaultDescription)),
            'canonicalUrl' => request()->url(),
            'shareImage' => asset('images/codeibex-mark.svg'),
            'analyticsId' => env('ANALYTICS_ID'),
            'platformTheme' => implode('; ', [
                '--platform-light: ' . $platformLight,
                '--platform-accent: ' . $platformAccent,
                '--platform-primary: ' . $platformPrimary,
                '--platform-dark: ' . $platformDark,
            ]),
        ]);
    }
}
