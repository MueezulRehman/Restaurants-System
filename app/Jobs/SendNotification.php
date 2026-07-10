<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Notification $notification,
        protected string $channel
    ) {}

    public function handle(): void
    {
        try {
            match ($this->channel) {
                'email' => $this->sendEmail(),
                'whatsapp' => $this->sendWhatsApp(),
                'push' => $this->sendPush(),
                default => null,
            };

            $this->notification->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (\Exception $e) {
            \Log::error("Failed to send notification: {$e->getMessage()}");
            $this->notification->update(['status' => 'failed']);
        }
    }

    private function sendEmail(): void
    {
        $recipient = $this->notification->user?->email ?? $this->notification->customer?->email;

        if ($recipient) {
            NotificationService::sendEmail($this->notification, $recipient);
        }
    }

    private function sendWhatsApp(): void
    {
        $phoneNumber = $this->notification->user?->phone ?? $this->notification->customer?->phone;

        if ($phoneNumber) {
            NotificationService::sendWhatsApp($this->notification, $phoneNumber);
        }
    }

    private function sendPush(): void
    {
        $subscriber = $this->notification->user ?? $this->notification->customer;

        if ($subscriber) {
            NotificationService::sendPush($this->notification, $subscriber);
        }
    }
}
