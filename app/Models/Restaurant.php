<?php

namespace App\Models;

use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_type_id',
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'custom_domain',
        'domain',
        'plan',
        'status',
        'show_on_homepage',
        'storefront_enabled',
        'storefront_module_override',
        'homepage_sort_order',
        'activated_at',
        'restricted',
        'logo_path',
        'theme',
        'opening_hours',
        'is_closed_today',
        'closed_message',
        'accept_orders_when_closed',
        'customer_template',
        'trial_ends_at',
        'enabled_modules',
        'db_connection',
        'pos_allow_short_payment_without_debt',
        'pos_short_payment_threshold',
        'storefront_notice',
        'storefront_notice_enabled',
    ];

    protected $casts = [
        'theme' => 'array',
        'opening_hours' => 'array',
        'trial_ends_at' => 'datetime',
        'activated_at' => 'datetime',
        'enabled_modules' => 'array',
        'db_connection' => 'encrypted:array',
        'restricted' => 'boolean',
        'show_on_homepage' => 'boolean',
        'storefront_enabled' => 'boolean',
        'storefront_module_override' => 'boolean',
        'homepage_sort_order' => 'integer',
        'is_closed_today' => 'boolean',
        'accept_orders_when_closed' => 'boolean',
        'storefront_notice_enabled' => 'boolean',
        'pos_allow_short_payment_without_debt' => 'boolean',
        'pos_short_payment_threshold' => 'integer',
    ];

    public function businessType()
    {
        return $this->belongsTo(BusinessType::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function domains()
    {
        return $this->hasMany(RestaurantDomain::class);
    }

    public function subscription()
    {
        return $this->hasOne(RestaurantSubscription::class);
    }

    public function restaurantTheme()
    {
        return $this->hasOne(RestaurantTheme::class);
    }

    /**
     * Check if a module is enabled for this restaurant.
     */
    public function isModuleEnabled(string $moduleKey): bool
    {
        $storefrontModules = ['menu', 'categories', 'variants', 'deals', 'item-sales', 'orders', 'delivery', 'storefront-notices'];
        if (in_array($moduleKey, $storefrontModules, true) && ! $this->storefront_enabled && ! $this->storefront_module_override) {
            return false;
        }

        if ($this->enabled_modules && is_array($this->enabled_modules) && count($this->enabled_modules) > 0) {
            return in_array($moduleKey, $this->enabled_modules, true);
        }

        $central = config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));
        $businessType = $this->business_type_id
            ? \App\Models\BusinessType::on($central)->find($this->business_type_id)
            : null;

        if (! $businessType) {
            return false;
        }

        return $businessType->modules()
            ->where('key', $moduleKey)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get all enabled modules for this restaurant.
     */
    public function getEnabledModules()
    {
        $central = config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));
        $storefrontModules = ['menu', 'categories', 'variants', 'deals', 'item-sales', 'orders', 'delivery', 'storefront-notices'];

        if ($this->enabled_modules && is_array($this->enabled_modules) && count($this->enabled_modules) > 0) {
            return Module::on($central)->whereIn('key', $this->enabled_modules)
                ->where('is_active', true)
                ->when(! $this->storefront_enabled && ! $this->storefront_module_override, fn($query) => $query->whereNotIn('key', $storefrontModules))
                ->orderBy('sort_order')
                ->get();
        }

        $businessType = $this->business_type_id
            ? \App\Models\BusinessType::on($central)->find($this->business_type_id)
            : null;

        if (! $businessType) {
            return collect();
        }

        return $businessType->modules()
            ->where('is_active', true)
            ->when(! $this->storefront_enabled && ! $this->storefront_module_override, fn($query) => $query->whereNotIn('key', $storefrontModules))
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Which POS UI this restaurant should see: 'restaurant', 'retail', or
     * 'medical'. Driven by business type name so admins never have to pick
     * it manually — assigning a business type is enough.
     */
    public function getPosMode(): string
    {
        $name = strtolower($this->businessType?->name ?? '');

        return config("pos.business_type_modes.{$name}", config('pos.default_mode'));
    }

    /**
     * The full labels/config array for this restaurant's POS mode.
     */
    public function getPosConfig(): array
    {
        $mode = $this->getPosMode();

        return array_merge(
            ['mode' => $mode],
            config("pos.modes.{$mode}", config('pos.modes.retail'))
        );
    }

    /**
     * POS config for this restaurant, merging platform defaults with
     * any per-restaurant overrides stored on the model.
     */
    public function getPosConfigForRestaurant(): array
    {
        $config = $this->getPosConfig();

        $config['allow_short_payment_without_debt'] = $this->pos_allow_short_payment_without_debt ?? config('pos.allow_short_payment_without_debt');
        $config['short_payment_threshold'] = $this->pos_short_payment_threshold ?? config('pos.short_payment_threshold');

        return $config;
    }

    public function getCustomerMenuTemplate(): string
    {
        $template = trim((string) ($this->customer_template ?: 'default'));
        $template = preg_replace('/\.blade(?:\.php)?$/i', '', $template) ?: 'default';

        return basename($template);
    }

    public static function getAvailableCustomerMenuTemplates(): array
    {
        $templateDir = resource_path('views/customer/menu_templates');
        $templates = [];

        if (! File::exists($templateDir)) {
            return ['default' => 'Default'];
        }

        foreach (File::files($templateDir) as $file) {
            $key = pathinfo($file, PATHINFO_FILENAME);
            $templates[$key] = ucwords(str_replace(['-', '_'], ' ', $key));
        }

        if (! isset($templates['default'])) {
            $templates = ['default' => 'Default'] + $templates;
        }

        return $templates;
    }

    /**
     * Get the public URL for this restaurant.
     *
     * If the restaurant has an explicit custom or domain hostname configured,
     * the public URL should use that domain. Otherwise fall back to the main
     * platform menu route using the restaurant slug.
     */
    public function getPublicUrl(): string
    {
        if ($this->domain) {
            return $this->formatRestaurantUrl($this->domain);
        }

        if ($this->custom_domain) {
            return $this->formatRestaurantUrl($this->custom_domain);
        }

        return route('menu.restaurant', $this->slug);
    }

    protected function formatRestaurantUrl(string $hostname): string
    {
        $hostname = trim($hostname);

        if (! preg_match('/^https?:\/\//', $hostname)) {
            $hostname = 'https://' . $hostname;
        }

        return rtrim($hostname, '/');
    }

    public function hasTenantDatabase(): bool
    {
        return is_array($this->db_connection) && count($this->db_connection) > 0;
    }

    public function getTenantDatabaseConfig(): array
    {
        return array_merge([
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
        ], $this->db_connection ?? []);
    }

    /**
     * Determine whether this restaurant storefront can be shown.
     */
    /**
     * Build the inline CSS custom-property overrides for this restaurant's
     * storefront theme (set on <body> in layouts/customer.blade.php).
     * Falls back to the original demo palette when no theme is saved yet,
     * and derives a couple of shades so buttons/hovers still look right.
     */
    public function effectiveTheme(?\Carbon\CarbonInterface $when = null): array
    {
        $theme = $this->restaurantTheme?->customerTheme();
        if (! is_array($theme) || $theme === []) {
            $theme = is_array($this->theme) ? $this->theme : [];
        }
        $base = [
            'primary' => $theme['primary'] ?? \App\Models\PlatformSetting::getValue('platform_theme_primary', '#2E5E99'),
            'secondary' => $theme['secondary'] ?? \App\Models\PlatformSetting::getValue('platform_theme_dark', '#0D2440'),
            'accent' => $theme['accent'] ?? \App\Models\PlatformSetting::getValue('platform_theme_accent', '#7BA4D0'),
            'light' => $theme['light'] ?? \App\Models\PlatformSetting::getValue('platform_theme_light', '#E7F0FA'),
        ];

        $when = $when ?? now();
        $schedule = is_array($theme['schedule'] ?? null) ? $theme['schedule'] : [];
        $day = strtolower($when->englishDayOfWeek);

        if (isset($schedule[$day]) && is_array($schedule[$day])) {
            return array_merge($base, array_filter($schedule[$day]));
        }

        if ($when->isWeekend() && isset($schedule['weekend']) && is_array($schedule['weekend'])) {
            return array_merge($base, array_filter($schedule['weekend']));
        }

        return $base;
    }

    public function isDailyThemeActive(): bool
    {
        $theme = $this->restaurantTheme?->customerTheme();
        if (! is_array($theme) || $theme === []) {
            $theme = is_array($this->theme) ? $this->theme : [];
        }
        $schedule = is_array($theme['schedule'] ?? null) ? $theme['schedule'] : [];

        if ($schedule === []) {
            return false;
        }

        $day = strtolower(now()->englishDayOfWeek);
        $isWeekend = now()->isWeekend();

        return isset($schedule[$day]) || ($isWeekend && isset($schedule['weekend']));
    }

    public function getStorefrontNotice(): ?string
    {
        $noticeRecord = $this->getActiveStorefrontNotice();
        $notice = trim((string) ($noticeRecord?->message ?? ''));

        if ($notice === '') {
            $notice = trim((string) ($this->storefront_notice ?? ''));
        }

        return $notice !== '' && ($noticeRecord || ($this->storefront_notice_enabled &&
            ! $this->getActiveStorefrontNotice())
        ) ? $notice : null;
    }

    public function getActiveStorefrontNotice(): ?\App\Models\StorefrontNotice
    {
        return \App\Models\StorefrontNotice::query()
            ->where('restaurant_id', $this->id)
            ->where('is_active', true)
            ->first();
    }

    public function themeCssVariables(): string
    {
        $theme = $this->effectiveTheme();

        $primary = $theme['primary'] ?? \App\Models\PlatformSetting::getValue('platform_theme_primary', '#2E5E99');
        $secondaryDark = $theme['secondary'] ?? \App\Models\PlatformSetting::getValue('platform_theme_dark', '#0D2440');
        $accent = $theme['accent'] ?? \App\Models\PlatformSetting::getValue('platform_theme_accent', '#7BA4D0');
        $light = $theme['light'] ?? \App\Models\PlatformSetting::getValue('platform_theme_light', '#E7F0FA');
        $tabBackground = $theme['tab_background'] ?? '#FFFFFF';
        $tabText = $theme['tab_text'] ?? '#64748B';
        $tabActive = $theme['tab_active'] ?? $primary;

        return implode('; ', [
            '--tenant-primary: ' . $primary,
            '--tenant-primary-light: ' . $primary,
            '--tenant-dark: ' . $secondaryDark,
            '--tenant-accent: ' . $accent,
            '--tenant-accent-dark: ' . $accent,
            '--tenant-cream: ' . $light,
            '--tenant-tab-background: ' . $tabBackground,
            '--tenant-tab-text: ' . $tabText,
            '--tenant-tab-active: ' . $tabActive,
        ]);
    }

    public function isPubliclyDiscoverable(): bool
    {
        return (bool) $this->show_on_homepage && $this->isStorefrontAvailable();
    }

    public function isStorefrontAvailable(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if (! $this->subscription) {
            return false;
        }

        if ($this->subscription->plan && ! $this->subscription->plan->is_active) {
            return false;
        }

        return $this->subscription->isActive() || $this->subscription->isInTrial();
    }
}
