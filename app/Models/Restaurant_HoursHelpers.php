<?php

/**
 * Add to App\Models\Restaurant — fillable, casts, and methods below.
 *
 * fillable add: opening_hours, is_closed_today, closed_message, accept_orders_when_closed
 * casts add:
 *   'opening_hours' => 'array',
 *   'is_closed_today' => 'boolean',
 *   'accept_orders_when_closed' => 'boolean',
 */

/*
    /**
     * Default weekly hours structure.
     * Each day: ['open' => '09:00', 'close' => '22:00', 'closed' => false]
     */
    public static function defaultOpeningHours(): array
    {
        $day = ['open' => '09:00', 'close' => '22:00', 'closed' => false];

        return [
            'monday' => $day,
            'tuesday' => $day,
            'wednesday' => $day,
            'thursday' => $day,
            'friday' => $day,
            'saturday' => $day,
            'sunday' => ['open' => '10:00', 'close' => '21:00', 'closed' => false],
        ];
    }

    public function getOpeningHoursNormalized(): array
    {
        $hours = is_array($this->opening_hours) ? $this->opening_hours : [];
        $defaults = static::defaultOpeningHours();

        foreach ($defaults as $day => $def) {
            if (! isset($hours[$day]) || ! is_array($hours[$day])) {
                $hours[$day] = $def;
            } else {
                $hours[$day] = array_merge($def, $hours[$day]);
            }
        }

        return $hours;
    }

    /**
     * Whether the business is open for online ordering right now.
     */
    public function isOpenNow(?\Carbon\CarbonInterface $when = null): bool
    {
        if ($this->is_closed_today) {
            return false;
        }

        $when = $when ?? now();
        $day = strtolower($when->englishDayOfWeek);
        $hours = $this->getOpeningHoursNormalized();
        $today = $hours[$day] ?? null;

        if (! $today || ! empty($today['closed'])) {
            return false;
        }

        $open = $today['open'] ?? '00:00';
        $close = $today['close'] ?? '23:59';

        try {
            $openAt = $when->copy()->setTimeFromTimeString($open);
            $closeAt = $when->copy()->setTimeFromTimeString($close);
        } catch (\Throwable $e) {
            return true; // fail open if bad config
        }

        // Overnight window (e.g. 18:00 – 02:00)
        if ($closeAt->lte($openAt)) {
            return $when->gte($openAt) || $when->lte($closeAt);
        }

        return $when->between($openAt, $closeAt);
    }

    /**
     * Can customers place an online order right now?
     */
    public function isAcceptingOnlineOrders(): bool
    {
        if (! $this->isStorefrontAvailable()) {
            return false;
        }

        if ($this->isOpenNow()) {
            return true;
        }

        // Manager opted to still accept (e.g. pre-orders) — rare
        return (bool) $this->accept_orders_when_closed;
    }

    public function openClosedLabel(): string
    {
        if ($this->is_closed_today) {
            return $this->closed_message ?: 'Closed today';
        }

        if ($this->isOpenNow()) {
            $day = strtolower(now()->englishDayOfWeek);
            $h = $this->getOpeningHoursNormalized()[$day] ?? [];
            $close = $h['close'] ?? '';

            return $close ? "Open · closes {$close}" : 'Open';
        }

        $day = strtolower(now()->englishDayOfWeek);
        $h = $this->getOpeningHoursNormalized()[$day] ?? [];
        if (! empty($h['closed'])) {
            return 'Closed today';
        }
        $open = $h['open'] ?? '';

        return $open ? "Closed · opens {$open}" : 'Closed';
    }

    public function nextOpenLabel(): ?string
    {
        if ($this->isOpenNow() && ! $this->is_closed_today) {
            return null;
        }

        $cursor = now()->startOfMinute();
        for ($i = 0; $i < 8; $i++) {
            $day = strtolower($cursor->englishDayOfWeek);
            $h = $this->getOpeningHoursNormalized()[$day] ?? [];
            if (empty($h['closed']) && ! empty($h['open'])) {
                if ($i === 0 && $this->is_closed_today) {
                    $cursor = $cursor->addDay()->startOfDay();
                    continue;
                }
                try {
                    $openAt = $cursor->copy()->setTimeFromTimeString($h['open']);
                    if ($openAt->gt(now()) || $i > 0) {
                        return 'Opens ' . $openAt->format('D H:i');
                    }
                } catch (\Throwable $e) {
                }
            }
            $cursor = $cursor->addDay()->startOfDay();
        }

        return null;
    }
*/
