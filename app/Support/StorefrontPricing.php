<?php

namespace App\Support;

use App\Models\ItemPromotion;
use App\Models\MenuItem;
use App\Models\MenuItemSize;

/**
 * Single place for public + server-side sale price resolution.
 *
 * @author Mueez Ul Rehman
 */
class StorefrontPricing
{
    /**
     * Resolve unit price for a menu item (optional size), applying live ItemPromotion.
     */
    public static function unitPriceForMenuItem(MenuItem $item, ?string $sizeLabel = null): float
    {
        $base = self::basePrice($item, $sizeLabel);
        $promo = self::livePromotion($item);

        if (! $promo) {
            return $base;
        }

        return $promo->applyTo($base);
    }

    public static function basePrice(MenuItem $item, ?string $sizeLabel = null): float
    {
        if ($item->has_sizes) {
            $size = $item->relationLoaded('sizes')
                ? $item->sizes->firstWhere('size_label', $sizeLabel)
                : MenuItemSize::where('menu_item_id', $item->id)->where('size_label', $sizeLabel)->first();

            return (float) ($size?->price ?? $item->price ?? 0);
        }

        return (float) ($item->price ?? 0);
    }

    public static function livePromotion(MenuItem $item): ?ItemPromotion
    {
        if (! class_exists(ItemPromotion::class)) {
            return null;
        }

        if ($item->relationLoaded('promotions')) {
            return $item->promotions->first(fn ($p) => $p->isLive());
        }

        return ItemPromotion::query()
            ->where('menu_item_id', $item->id)
            ->currentlyActive()
            ->orderByDesc('id')
            ->first();
    }

    public static function saleLabel(MenuItem $item): ?string
    {
        $promo = self::livePromotion($item);
        if (! $promo) {
            return null;
        }
        if ($promo->type === 'percent') {
            return rtrim(rtrim(number_format((float) $promo->value, 2), '0'), '.') . '% OFF';
        }

        return 'Rs ' . number_format((float) $promo->value, 0) . ' OFF';
    }
}
