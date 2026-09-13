<?php

namespace App\Console\Commands;

use App\Models\GymMembership;
use App\Models\Notification;
use App\Models\Restaurant;
use App\Models\ServicePackagePurchase;
use App\Services\NotificationService;
use App\Support\Tenancy;
use Illuminate\Console\Command;
use Throwable;

class SendExpiryRemindersCommand extends Command
{
    protected $signature = 'businesses:send-expiry-reminders {--days=7 : Notify records expiring within this many days}';
    protected $description = 'Send reminders for expiring gym memberships and salon service packages';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $success = 0;
        $failed = 0;

        Restaurant::query()->orderBy('id')->each(function (Restaurant $restaurant) use ($days, &$success, &$failed): void {
            try {
                Tenancy::runFor($restaurant, function () use ($restaurant, $days, &$success): void {
                    $until = now()->addDays($days)->endOfDay();
                    $memberships = GymMembership::with('customer')->where('restaurant_id', $restaurant->id)->where('status', 'active')->whereBetween('ends_at', [today(), $until])->get();
                    $packages = ServicePackagePurchase::with(['customer', 'package'])->where('restaurant_id', $restaurant->id)->where('status', 'active')->where('remaining_visits', '>', 0)->whereBetween('ends_at', [today(), $until])->get();

                    foreach ($memberships as $membership) {
                        $this->sendOnce($restaurant, 'membership_expiry', $membership->customer, 'Membership expiring soon', "Your gym membership expires on {$membership->ends_at->format('d M Y')}.", $success);
                    }
                    foreach ($packages as $purchase) {
                        $this->sendOnce($restaurant, 'service_package_expiry', $purchase->customer, 'Service package expiring soon', "Your {$purchase->package->name} package expires on {$purchase->ends_at->format('d M Y')}. Remaining visits: {$purchase->remaining_visits}.", $success);
                    }
                });
            } catch (Throwable $exception) {
                $failed++;
                $this->error("Business #{$restaurant->id} failed: {$exception->getMessage()}");
            } finally {
                Tenancy::end();
            }
        });

        $this->info("Expiry reminders sent: {$success}; failed businesses: {$failed}.");
        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function sendOnce(Restaurant $restaurant, string $type, $customer, string $title, string $message, int &$success): void
    {
        if (! $customer) return;
        $alreadySent = Notification::where('restaurant_id', $restaurant->id)->where('customer_id', $customer->id)->where('type', $type)->where('created_at', '>=', now()->subDays(3))->exists();
        if ($alreadySent) return;
        $channels = $customer->email ? ['email'] : ($customer->phone ? ['whatsapp'] : []);
        if ($channels === []) return;
        NotificationService::send($restaurant->id, $type, $title, $message, $channels, null, $customer);
        $success++;
    }
}
