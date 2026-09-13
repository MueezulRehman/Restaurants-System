<?php

namespace App\Services;

use App\Contracts\NotificationProvider;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TwilioNotificationProvider implements NotificationProvider
{
    public function send(string $channel, string $recipient, string $message): ?string
    {
        $config = config('services.twilio');
        $sid = $config['sid'] ?? null;
        $token = $config['token'] ?? null;
        $from = $channel === 'whatsapp' ? ($config['whatsapp_from'] ?? null) : ($config['from'] ?? null);

        if (! in_array($channel, ['sms', 'whatsapp'], true) || blank($sid) || blank($token) || blank($from)) {
            throw new RuntimeException('Twilio notification configuration is incomplete.');
        }

        $to = $channel === 'whatsapp' ? 'whatsapp:' . $recipient : $recipient;
        $endpoint = 'https://api.twilio.com/2010-04-01/Accounts/' . rawurlencode($sid) . '/Messages.json';

        return Http::asForm()
            ->withBasicAuth($sid, $token)
            ->post($endpoint, [
                'From' => $channel === 'whatsapp' ? 'whatsapp:' . $from : $from,
                'To' => $to,
                'Body' => $message,
            ])
            ->throw()
            ->json('sid');
    }
}
