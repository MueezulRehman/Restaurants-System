<?php

namespace App\Models;

use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'logo_path',
        'theme',
        'trial_ends_at',
        'enabled_modules',
        'db_connection',
    ];

    protected $casts = [
        'theme' => 'array',
        'trial_ends_at' => 'datetime',
        'enabled_modules' => 'array',
        'db_connection' => 'encrypted:array',
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

    /**
     * Check if a module is enabled for this restaurant.
     */
    public function isModuleEnabled(string $moduleKey): bool
    {
        if ($this->enabled_modules && is_array($this->enabled_modules) && count($this->enabled_modules) > 0) {
            return in_array($moduleKey, $this->enabled_modules, true);
        }

        if (!$this->businessType) {
            return false;
        }

        return $this->businessType->modules()
            ->where('key', $moduleKey)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get all enabled modules for this restaurant.
     */
    public function getEnabledModules()
    {
        if ($this->enabled_modules && is_array($this->enabled_modules) && count($this->enabled_modules) > 0) {
            return Module::whereIn('key', $this->enabled_modules)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        }

        if (!$this->businessType) {
            return collect();
        }

        return $this->businessType->modules()
            ->where('is_active', true)
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
