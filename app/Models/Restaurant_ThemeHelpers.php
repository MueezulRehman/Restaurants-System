<?php

/**
 * Add to App\Models\Restaurant — effective theme for today (schedule aware).
 */

/*
    /**
     * Theme colors for "today" — uses schedule.monday…sunday or schedule.weekend.
     */
    public function effectiveTheme(?\Carbon\CarbonInterface $when = null): array
    {
        $theme = is_array($this->theme) ? $this->theme : [];
        $base = [
            'primary' => $theme['primary'] ?? '#0f3d2e',
            'secondary' => $theme['secondary'] ?? '#c9a227',
            'accent' => $theme['accent'] ?? '#16a34a',
        ];

        $when = $when ?? now();
        $schedule = is_array($theme['schedule'] ?? null) ? $theme['schedule'] : [];
        $day = strtolower($when->englishDayOfWeek); // monday …

        if (isset($schedule[$day]) && is_array($schedule[$day])) {
            return array_merge($base, array_filter($schedule[$day]));
        }

        if ($when->isWeekend() && isset($schedule['weekend']) && is_array($schedule['weekend'])) {
            return array_merge($base, array_filter($schedule['weekend']));
        }

        return $base;
    }

    /**
     * Whether this business currently has any live item promotion.
     */
    public function hasLiveSales(): bool
    {
        if (! class_exists(\App\Models\ItemPromotion::class)) {
            return false;
        }

        return \App\Models\ItemPromotion::query()
            ->where('restaurant_id', $this->id)
            ->currentlyActive()
            ->exists();
    }

    public function themeCssVariables(): string
    {
        $t = $this->effectiveTheme();
        $primary = $t['primary'] ?? '#166534';
        $secondary = $t['secondary'] ?? '#0B3D24';
        $accent = $t['accent'] ?? '#FACC15';

        return implode('; ', [
            '--tenant-primary: ' . $primary,
            '--tenant-primary-light: ' . $primary,
            '--tenant-dark: ' . $secondary,
            '--tenant-accent: ' . $accent,
            '--tenant-accent-dark: ' . $accent,
            '--tenant-cream: #FFFBEB',
        ]);
    }
*/
