<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function sendText($phone, $message)
    {
        try {
            $normalizedPhone = $this->normalizePhoneNumber($phone);
            $apiUrl = rtrim((string) config('services.whatsapp.api_url'), '/');
            $token = config('services.whatsapp.token');
            $from = config('services.whatsapp.from');

            if (empty($normalizedPhone) || empty($message) || empty($apiUrl) || empty($token) || empty($from)) {
                Log::info('WhatsApp message skipped because the gateway is not fully configured.', [
                    'phone' => $normalizedPhone,
                ]);

                return true;
            }

            $response = Http::withToken($token)
                ->post($apiUrl . '/' . $from . '/messages', [
                    'messaging_product' => 'whatsapp',
                    'to' => $normalizedPhone,
                    'type' => 'text',
                    'text' => [
                        'body' => $message,
                    ],
                ]);

            if ($response->failed()) {
                Log::error('WhatsApp Failed', [
                    'response' => $response->json(),
                ]);
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('WhatsApp Exception', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send an image with optional caption (Cloud API).
     * Uploads the local file, then sends by media id so each customer can get a unique card.
     */
    public function sendImage(string $phone, string $absolutePath, ?string $caption = null)
    {
        try {
            $normalizedPhone = $this->normalizePhoneNumber($phone);
            $apiUrl = rtrim((string) config('services.whatsapp.api_url'), '/');
            $token = config('services.whatsapp.token');
            $from = config('services.whatsapp.from');

            if (empty($normalizedPhone) || empty($apiUrl) || empty($token) || empty($from)) {
                Log::info('WhatsApp image skipped — gateway not configured.', compact('normalizedPhone'));

                return true;
            }

            if (! is_file($absolutePath)) {
                Log::warning('WhatsApp image file missing', ['path' => $absolutePath]);

                return false;
            }

            $mediaId = $this->uploadMedia($absolutePath, 'image/png');
            if (! $mediaId) {
                return false;
            }

            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $normalizedPhone,
                'type' => 'image',
                'image' => array_filter([
                    'id' => $mediaId,
                    'caption' => $caption ? mb_substr($caption, 0, 1024) : null,
                ]),
            ];

            $response = Http::withToken($token)
                ->post($apiUrl . '/' . $from . '/messages', $payload);

            if ($response->failed()) {
                Log::error('WhatsApp image send failed', ['response' => $response->json()]);
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('WhatsApp image exception', ['message' => $e->getMessage()]);

            return false;
        }
    }

    protected function uploadMedia(string $absolutePath, string $mime): ?string
    {
        $apiUrl = rtrim((string) config('services.whatsapp.api_url'), '/');
        $token = config('services.whatsapp.token');
        $from = config('services.whatsapp.from');

        // Meta Cloud API: POST /{phone-number-id}/media
        $response = Http::withToken($token)
            ->attach('file', file_get_contents($absolutePath), basename($absolutePath))
            ->post($apiUrl . '/' . $from . '/media', [
                'messaging_product' => 'whatsapp',
                'type' => $mime,
            ]);

        if ($response->failed()) {
            Log::error('WhatsApp media upload failed', ['response' => $response->json()]);

            return null;
        }

        return $response->json('id');
    }

    protected function normalizePhoneNumber($phoneNumber): string
    {
        if (! is_string($phoneNumber) && ! is_numeric($phoneNumber)) {
            return '';
        }

        $countryCode = (string) config('services.whatsapp.default_country_code', '92');
        $digits = preg_replace('/\D/', '', (string) $phoneNumber);

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, $countryCode)) {
            return '+' . $digits;
        }

        if (str_starts_with($digits, '0')) {
            $digits = $countryCode . substr($digits, 1);
        } elseif (! str_starts_with($digits, $countryCode)) {
            $digits = $countryCode . $digits;
        }

        return '+' . $digits;
    }
}
