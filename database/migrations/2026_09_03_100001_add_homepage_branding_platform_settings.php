<?php

use App\Models\PlatformSetting;
use Illuminate\Database\Migrations\Migration;

/**
 * Seed homepage branding keys for Super Admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            'homepage_hero_title' => 'Discover Restaurants & Local Businesses',
            'homepage_hero_subtitle' => 'Search, choose a business, browse the menu, and place your order in seconds.',
            'homepage_banner_image' => '',
            'homepage_sale_badge_text' => 'Sale live',
            'homepage_show_sale_badges' => '1',
        ];

        foreach ($defaults as $key => $value) {
            if (class_exists(PlatformSetting::class)) {
                $exists = PlatformSetting::query()->where('key', $key)->exists();
                if (! $exists) {
                    PlatformSetting::setValue($key, $value);
                }
            }
        }
    }

    public function down(): void
    {
        // keys left in place intentionally
    }
};
