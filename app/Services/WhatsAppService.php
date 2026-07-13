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