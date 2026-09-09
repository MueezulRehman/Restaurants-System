<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantTheme extends Model
{
    protected $fillable = [
        'restaurant_id',
        'manager_light',
        'manager_dark',
        'customer',
    ];

    protected $casts = [
        'manager_light' => 'array',
        'manager_dark' => 'array',
        'customer' => 'array',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public static function defaults(): array
    {
        return [
            'manager_light' => [
                'background' => '#E7F0FA',
                'surface' => '#E7F0FA',
                'primary' => '#2E5E99',
                'accent' => '#7BA4D0',
                'dark' => '#0D2440',
            ],
            'manager_dark' => [
                'background' => '#0F172A',
                'surface' => '#0F172A',
                'primary' => '#1D4ED8',
                'accent' => '#93C5FD',
                'dark' => '#0B1220',
            ],
            'customer' => [
                'primary' => '#2E5E99',
                'secondary' => '#0D2440',
                'accent' => '#7BA4D0',
                'light' => '#E7F0FA',
                'tab_background' => '#FFFFFF',
                'tab_text' => '#64748B',
                'tab_active' => '#2E5E99',
                'schedule' => [],
                'hero_slides' => [],
                'preset' => 'codeibex',
            ],
        ];
    }

    public static function customerPresets(): array
    {
        return [
            'codeibex' => ['CodeIbex Blue', ['#2E5E99', '#0D2440', '#7BA4D0', '#E7F0FA']],
            'emerald' => ['Emerald', ['#166534', '#052E16', '#4ADE80', '#ECFDF5']],
            'royal' => ['Royal', ['#6D28D9', '#24104F', '#C4B5FD', '#F5F3FF']],
            'midnight' => ['Midnight Teal', ['#0F766E', '#042F2E', '#5EEAD4', '#F0FDFA']],
            'sunset' => ['Sunset', ['#C2410C', '#431407', '#FDBA74', '#FFF7ED']],
        ];
    }

    public static function managerPresets(): array
    {
        return [
            'codeibex' => ['light' => ['#E7F0FA', '#2E5E99', '#7BA4D0', '#0D2440'], 'dark' => ['#0F172A', '#1D4ED8', '#93C5FD', '#0B1220']],
            'emerald' => ['light' => ['#ECFDF5', '#166534', '#4ADE80', '#052E16'], 'dark' => ['#071F16', '#15803D', '#86EFAC', '#052E16']],
            'royal' => ['light' => ['#F5F3FF', '#6D28D9', '#C4B5FD', '#24104F'], 'dark' => ['#171126', '#7C3AED', '#C4B5FD', '#100A1F']],
            'midnight' => ['light' => ['#F0FDFA', '#0F766E', '#5EEAD4', '#042F2E'], 'dark' => ['#042F2E', '#0F766E', '#5EEAD4', '#021C1B']],
            'sunset' => ['light' => ['#FFF7ED', '#C2410C', '#FDBA74', '#431407'], 'dark' => ['#27120A', '#EA580C', '#FDBA74', '#431407']],
        ];
    }

    public function managerPalette(string $mode = 'light'): array
    {
        $key = $mode === 'dark' ? 'manager_dark' : 'manager_light';
        $palette = array_merge(self::defaults()[$key], is_array($this->{$key}) ? $this->{$key} : []);
        if (empty($this->{$key}['surface'] ?? null)) {
            $palette['surface'] = $palette['background'];
        }

        return $palette;
    }

    public function customerTheme(): array
    {
        return array_merge(self::defaults()['customer'], is_array($this->customer) ? $this->customer : []);
    }
}
