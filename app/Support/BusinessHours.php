<?php

namespace App\Support;

use App\Models\Restaurant;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Single place for open/closed decisions used by menu, checkout, homepage.
 *
 * Supports:
 * - Weekly schedule (per day open/close or day-off)
 * - Closed today (full day off)
 * - Early close today (close at a specific time today)
 * - Extend hours today (stay open later than schedule)
 *
 * @author Mueez Ul Rehman
 */
class BusinessHours
{
    public static function defaultWeek(): array
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

    public static function normalized(Restaurant $r): array
    {
        $hours = is_array($r->opening_hours) ? $r->opening_hours : [];
        $defaults = self::defaultWeek();
        foreach ($defaults as $day => $def) {
            $hours[$day] = isset($hours[$day]) && is_array($hours[$day])
                ? array_merge($def, $hours[$day])
                : $def;
        }

        return $hours;
    }

    /**
     * Effective open/close Carbon instances for a given calendar day.
     *
     * @return array{closed: bool, open: ?Carbon, close: ?Carbon, source: string}
     */
    public static function windowForDate(Restaurant $r, CarbonInterface $date): array
    {
        $day = strtolower($date->englishDayOfWeek);
        $hours = self::normalized($r);
        $today = $hours[$day];

        // Full day off (weekly flag)
        if (! empty($today['closed'])) {
            return ['closed' => true, 'open' => null, 'close' => null, 'source' => 'weekly_closed'];
        }

        // Manager: closed today toggle (whole day)
        if ($r->is_closed_today && $date->isSameDay(now())) {
            return ['closed' => true, 'open' => null, 'close' => null, 'source' => 'closed_today'];
        }

        try {
            $open = $date->copy()->setTimeFromTimeString($today['open'] ?? '09:00');
            $close = $date->copy()->setTimeFromTimeString($today['close'] ?? '22:00');
        } catch (\Throwable $e) {
            return ['closed' => false, 'open' => null, 'close' => null, 'source' => 'invalid_schedule'];
        }

        // Same-day overrides only apply to "today"
        if ($date->isSameDay(now())) {
            // Early close: closes earlier than schedule
            if ($r->early_close_at) {
                $early = Carbon::parse($r->early_close_at);
                if ($early->isSameDay($date) && $early->lt($close)) {
                    $close = $early;
                    $source = 'early_close';
                }
            }
            // Extend hours: stay open later
            if ($r->extend_close_at) {
                $extend = Carbon::parse($r->extend_close_at);
                if ($extend->isSameDay($date) && $extend->gt($close)) {
                    $close = $extend;
                    $source = 'extended';
                }
            }
        }

        return [
            'closed' => false,
            'open' => $open,
            'close' => $close,
            'source' => $source ?? 'weekly',
        ];
    }

    public static function isOpenNow(Restaurant $r, ?CarbonInterface $when = null): bool
    {
        $when = $when ?? now();
        $win = self::windowForDate($r, $when);

        if ($win['closed'] || ! $win['open'] || ! $win['close']) {
            return false;
        }

        $open = $win['open'];
        $close = $win['close'];

        // Overnight (e.g. 18:00 – 02:00 next day)
        if ($close->lte($open)) {
            return $when->gte($open) || $when->lte($close);
        }

        return $when->between($open, $close);
    }

    public static function isAcceptingOnlineOrders(Restaurant $r, ?CarbonInterface $when = null): bool
    {
        // Subscription / platform gate
        if (method_exists($r, 'isStorefrontAvailable') && ! $r->isStorefrontAvailable()) {
            return false;
        }

        if (self::isOpenNow($r, $when)) {
            return true;
        }

        return (bool) ($r->accept_orders_when_closed ?? false);
    }

    public static function label(Restaurant $r, ?CarbonInterface $when = null): string
    {
        $when = $when ?? now();

        if ($r->is_closed_today) {
            return $r->closed_message ?: 'Closed today';
        }

        $win = self::windowForDate($r, $when);

        if ($win['closed']) {
            return 'Closed today';
        }

        if (self::isOpenNow($r, $when)) {
            $close = $win['close']?->format('H:i');

            return $close ? "Open · closes {$close}" : 'Open';
        }

        $open = $win['open']?->format('H:i');

        return $open ? "Closed · opens {$open}" : 'Closed';
    }

    public static function nextOpenLabel(Restaurant $r): ?string
    {
        if (self::isOpenNow($r) && ! $r->is_closed_today) {
            return null;
        }

        $cursor = now()->copy();
        for ($i = 0; $i < 8; $i++) {
            $dayCursor = $i === 0 ? $cursor : $cursor->copy()->addDays($i)->startOfDay();
            if ($i > 0) {
                // closed_today only affects current calendar day
            }
            $win = self::windowForDate($r, $dayCursor);
            // windowForDate uses is_closed_today only when date is today — good
            if ($win['closed'] || ! $win['open']) {
                continue;
            }
            $openAt = $win['open'];
            if ($openAt->gt(now())) {
                return 'Opens ' . $openAt->format('D H:i');
            }
            // If today's open is past but we're closed due to early close, try tomorrow
        }

        return null;
    }

    /**
     * Clear same-day overrides after midnight (scheduler).
     */
    public static function resetDailyFlags(Restaurant $r): void
    {
        $r->forceFill([
            'is_closed_today' => false,
            'early_close_at' => null,
            'extend_close_at' => null,
            // keep closed_message for reuse or clear:
            // 'closed_message' => null,
        ])->save();
    }
}
