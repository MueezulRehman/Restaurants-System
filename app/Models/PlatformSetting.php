<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Key/value platform settings for Codeibex SaaS.
 * @author Mueez Ul Rehman
 */
class PlatformSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, $default = null)
    {
        try {
            $all = Cache::remember('codeibex_platform_settings', 60, function () {
                return static::query()->pluck('value', 'key')->toArray();
            });
        } catch (\Throwable $e) {
            return $default;
        }

        return $all[$key] ?? $default;
    }

    public static function setValue(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('codeibex_platform_settings');
    }

    public static function bankDetails(): array
    {
        return [
            'bank_name' => (string) static::getValue('bank_name', ''),
            'account_title' => (string) static::getValue('bank_account_title', ''),
            'account_number' => (string) static::getValue('bank_account_number', ''),
            'iban' => (string) static::getValue('bank_iban', ''),
            'branch' => (string) static::getValue('bank_branch', ''),
            'instructions' => (string) static::getValue('bank_instructions', ''),
        ];
    }
}
