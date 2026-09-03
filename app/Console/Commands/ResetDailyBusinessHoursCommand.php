<?php

namespace App\Console\Commands;

use App\Models\Restaurant;
use App\Support\BusinessHours;
use Illuminate\Console\Command;

/**
 * Clears "closed today", early close, and extend-hours flags after midnight.
 *
 * Schedule in routes/console.php or Kernel:
 *   Schedule::command('business:reset-daily-hours')->dailyAt('00:05');
 *
 * @author Mueez Ul Rehman
 */
class ResetDailyBusinessHoursCommand extends Command
{
    protected $signature = 'business:reset-daily-hours';

    protected $description = 'Reset same-day closed/early-close/extend flags for all businesses';

    public function handle(): int
    {
        $count = 0;
        Restaurant::query()
            ->where(function ($q) {
                $q->where('is_closed_today', true)
                    ->orWhereNotNull('early_close_at')
                    ->orWhereNotNull('extend_close_at');
            })
            ->orderBy('id')
            ->chunkById(100, function ($rows) use (&$count) {
                foreach ($rows as $r) {
                    BusinessHours::resetDailyFlags($r);
                    $count++;
                }
            });

        $this->info("Reset daily hour flags on {$count} business(es).");

        return self::SUCCESS;
    }
}
