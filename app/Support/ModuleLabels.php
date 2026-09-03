<?php

namespace App\Support;

use App\Models\Restaurant;

/**
 * Resolve module display label/icon for the current business type.
 *
 * @author Mueez Ul Rehman
 */
class ModuleLabels
{
    public static function for(?Restaurant $restaurant, string $moduleKey): array
    {
        $defaults = config('module_labels.default', []);
        $base = $defaults[$moduleKey] ?? [
            'label' => str_replace(['-', '_'], ' ', ucfirst($moduleKey)),
            'icon' => 'fa-circle',
        ];

        $typeName = strtolower(trim((string) ($restaurant?->businessType?->name ?? '')));

        if ($typeName === '') {
            return $base;
        }

        $types = config('module_labels.types', []);

        // Exact match
        if (isset($types[$typeName][$moduleKey])) {
            return array_merge($base, $types[$typeName][$moduleKey]);
        }

        // Partial match (e.g. "Cafe / Bakery")
        foreach ($types as $key => $map) {
            if ($key !== '' && (str_contains($typeName, $key) || str_contains($key, $typeName))) {
                if (isset($map[$moduleKey])) {
                    return array_merge($base, $map[$moduleKey]);
                }
            }
        }

        return $base;
    }

    public static function label(?Restaurant $restaurant, string $moduleKey): string
    {
        return (string) (static::for($restaurant, $moduleKey)['label'] ?? $moduleKey);
    }

    public static function icon(?Restaurant $restaurant, string $moduleKey): string
    {
        return (string) (static::for($restaurant, $moduleKey)['icon'] ?? 'fa-circle');
    }
}
