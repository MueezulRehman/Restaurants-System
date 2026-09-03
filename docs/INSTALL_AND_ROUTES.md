# Business hours (full manager control) + stock audit notes

## Install

```bash
cp app/Support/BusinessHours.php app/Support/
cp app/Console/Commands/ResetDailyBusinessHoursCommand.php app/Console/Commands/
cp app/Http/Controllers/Admin/BusinessHoursController.php app/Http/Controllers/Admin/
cp database/migrations/2026_09_03_120000_extend_business_hours_controls.php database/migrations/
cp resources/views/admin/restaurant-profile/hours.blade.php resources/views/admin/restaurant-profile/

php artisan migrate
```

## Restaurant model

Add fillable: `opening_hours`, `is_closed_today`, `closed_message`, `accept_orders_when_closed`, `early_close_at`, `extend_close_at`

Casts:
```php
'opening_hours' => 'array',
'is_closed_today' => 'boolean',
'accept_orders_when_closed' => 'boolean',
'early_close_at' => 'datetime',
'extend_close_at' => 'datetime',
```

Delegate helpers:
```php
public function isOpenNow(): bool { return \App\Support\BusinessHours::isOpenNow($this); }
public function isAcceptingOnlineOrders(): bool { return \App\Support\BusinessHours::isAcceptingOnlineOrders($this); }
public function openClosedLabel(): string { return \App\Support\BusinessHours::label($this); }
public function nextOpenLabel(): ?string { return \App\Support\BusinessHours::nextOpenLabel($this); }
```

## Routes (manager group)

```php
use App\Http\Controllers\Admin\BusinessHoursController;

Route::get('/hours', [BusinessHoursController::class, 'edit'])->name('hours.edit');
Route::post('/hours/weekly', [BusinessHoursController::class, 'updateWeekly'])->name('hours.weekly');
Route::post('/hours/today', [BusinessHoursController::class, 'updateToday'])->name('hours.today');
```

Sidebar: link to `manager.hours.edit`.

## Scheduler (automated daily reset)

`routes/console.php`:
```php
use Illuminate\Support\Facades\Schedule;
Schedule::command('business:reset-daily-hours')->dailyAt('00:05');
```

This is the “automated opening hours sync” for same-day flags — weekly schedule stays; temporary flags clear each night.

## Checkout gate

```php
if (! $restaurant->isAcceptingOnlineOrders()) {
    return back()->withErrors([
        'cart' => $restaurant->closed_message
            ?: ('Closed right now. ' . ($restaurant->nextOpenLabel() ?? '')),
    ]);
}
```

## Manager capabilities

| Action | Result |
|--------|--------|
| Weekly open/close per day | Default schedule |
| Day off checkbox | That weekday never accepts |
| Closed today | Block all day + message |
| Early close 18:00 | Accept until 18:00 only today |
| Extend until 23:30 | Accept later than schedule today |
| Clear overrides | Back to weekly only |
| Accept when closed | Optional pre-orders |

## Stock audit

See `docs/STOCK_ADJUSTMENT_AUDIT.md` — schema drift noted; one `StockAudit` writer recommended; online stock only on confirm.
