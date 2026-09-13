<?php

namespace App\Services;

use App\Contracts\NotificationProvider;
use Illuminate\Support\Facades\Log;

class LogNotificationProvider implements NotificationProvider
{
    public function send(string $channel, string $recipient, string $message): void
    {
        Log::info('Queue notification sent by log provider.', [
            'channel' => $channel,
            'recipient' => $recipient,
            'message' => $message,
        ]);
    }
}
