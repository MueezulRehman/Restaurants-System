<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SubscriptionManager;

class CheckExpiredSubscriptions extends Command
{
    protected $signature = 'subscriptions:check-expired';
    protected $description = 'Check and mark expired subscriptions';

    public function handle()
    {
        $this->info('Checking for expired subscriptions...');

        $stats = SubscriptionManager::checkExpiredSubscriptions();

        $this->info("Expired trials: {$stats['expired_trials']}");
        $this->info("Expired periods: {$stats['expired_periods']}");
        $this->info("Total expired: {$stats['total']}");

        return Command::SUCCESS;
    }
}
