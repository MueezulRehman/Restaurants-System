<?php

/**
 * Add these methods to App\Models\MenuItem (copy into the class).
 *
 * Relations + live sale price helpers for storefront + POS.
 */

/*
    public function promotions()
    {
        return $this->hasMany(\App\Models\ItemPromotion::class);
    }

    public function activePromotion(): ?\App\Models\ItemPromotion
    {
        return $this->promotions()->currentlyActive()->orderByDesc('id')->first();
    }

    /**
     * Sale price if a live promotion exists, otherwise base display price.
     */
    public function getSalePriceAttribute(): float
    {
        $base = (float) ($this->display_price ?? $this->price ?? 0);
        $promo = $this->relationLoaded('promotions')
            ? $this->promotions->first(fn ($p) => $p->isLive())
            : $this->activePromotion();

        if (! $promo) {
            return $base;
        }

        return $promo->applyTo($base);
    }

    public function getHasActiveSaleAttribute(): bool
    {
        $promo = $this->relationLoaded('promotions')
            ? $this->promotions->first(fn ($p) => $p->isLive())
            : $this->activePromotion();

        return $promo !== null;
    }

    public function getSaleLabelAttribute(): ?string
    {
        $promo = $this->relationLoaded('promotions')
            ? $this->promotions->first(fn ($p) => $p->isLive())
            : $this->activePromotion();

        if (! $promo) {
            return null;
        }

        if ($promo->type === 'percent') {
            return rtrim(rtrim(number_format((float) $promo->value, 2), '0'), '.') . '% OFF';
        }

        return 'Rs ' . number_format((float) $promo->value, 0) . ' OFF';
    }
*/
