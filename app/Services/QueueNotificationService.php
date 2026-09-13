<?php

namespace App\Services;

use App\Jobs\SendQueueNotification;
use App\Models\QueueEntry;
use App\Models\Restaurant;

class QueueNotificationService
{
    /**
     * Build the public-safe message that future SMS/WhatsApp providers may send.
     *
     * Patient identity and medical details are intentionally excluded.
     */
    public static function calledMessage(QueueEntry $entry): string
    {
        $doctorName = $entry->doctor?->name;
        $doctorText = $doctorName ? " by {$doctorName}" : '';

        return "Queue token {$entry->token_number} is now being called{$doctorText}. Please proceed.";
    }

    public static function dispatchCalled(QueueEntry $entry): void
    {
        $restaurant = Restaurant::find($entry->restaurant_id);
        if (! $restaurant?->queue_notifications_enabled || ! $entry->patient?->notification_consent || blank($entry->patient?->phone)) {
            return;
        }

        foreach ($restaurant->queue_notification_channels ?? [] as $channel) {
            if (in_array($channel, ['sms', 'whatsapp'], true)) {
                SendQueueNotification::dispatch((int) $entry->restaurant_id, (int) $entry->getKey(), $channel);
            }
        }
    }
}
