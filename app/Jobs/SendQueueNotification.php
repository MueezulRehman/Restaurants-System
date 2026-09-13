<?php

namespace App\Jobs;

use App\Contracts\NotificationProvider;
use App\Models\QueueEntry;
use App\Models\QueueNotificationDelivery;
use App\Models\Restaurant;
use App\Services\QueueNotificationService;
use App\Support\Tenancy;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendQueueNotification extends TenantAwareJob
{
    public int $tries = 3;

    public function __construct(
        public int $restaurantId,
        public int $queueEntryId,
        public string $channel,
        public ?int $deliveryId = null,
    ) {
    }

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handleTenant(): void
    {
        $restaurant = Restaurant::findOrFail($this->restaurantId);
        if (! $restaurant->queue_notifications_enabled
            || ! in_array($this->channel, $restaurant->queue_notification_channels ?? [], true)) {
            return;
        }

        $entry = QueueEntry::with(['patient', 'doctor'])->where('restaurant_id', $this->restaurantId)->findOrFail($this->queueEntryId);
        $patient = $entry->patient;
        if (! $patient || ! $patient->notification_consent || blank($patient->phone)) {
            return;
        }

        $message = QueueNotificationService::calledMessage($entry);
        if (! $this->deliveryId) {
            $delivery = QueueNotificationDelivery::create([
                'restaurant_id' => $this->restaurantId,
                'queue_entry_id' => $this->queueEntryId,
                'channel' => $this->channel,
                'recipient_masked' => $this->maskedRecipient((string) $patient->phone),
                'status' => 'queued',
            ]);
            $this->deliveryId = $delivery->id;
        }

        $providerMessageId = app(NotificationProvider::class)->send($this->channel, (string) $patient->phone, $message);
        QueueNotificationDelivery::whereKey($this->deliveryId)->update([
            'provider_message_id' => $providerMessageId,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        Log::debug('Queue notification job completed.', [
            'restaurant_id' => $this->restaurantId,
            'queue_entry_id' => $this->queueEntryId,
            'channel' => $this->channel,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        if ($this->deliveryId) {
            Tenancy::forRestaurantId($this->restaurantId, function () use ($exception): void {
                QueueNotificationDelivery::whereKey($this->deliveryId)->update([
                    'status' => 'failed',
                    'error' => mb_substr($exception->getMessage(), 0, 1000),
                ]);
            });
        }

        Log::error('Queue notification delivery failed after retries.', [
            'restaurant_id' => $this->restaurantId,
            'queue_entry_id' => $this->queueEntryId,
            'channel' => $this->channel,
            'exception' => $exception->getMessage(),
        ]);
    }

    private function maskedRecipient(string $recipient): string
    {
        return strlen($recipient) > 4
            ? str_repeat('*', max(0, strlen($recipient) - 4)) . substr($recipient, -4)
            : '****';
    }
}
