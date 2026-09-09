<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function sendOtp(string $phone, string $code, string $businessName): bool
    {
        $driver = (string) config('services.sms.driver', 'log');
        $message = sprintf('%s verification code: %s. It expires in 5 minutes. Do not share this code.', $businessName, $code);

        if ($driver === 'log') {
            Log::info('OTP SMS (log driver)', [
                'phone' => $phone,
                'message' => $message,
            ]);

            return true;
        }

        $endpoint = (string) config('services.sms.endpoint');
        $token = (string) config('services.sms.token');
        if ($endpoint === '') {
            Log::warning('OTP SMS skipped because SMS_ENDPOINT is not configured.', ['phone' => $phone]);

            return false;
        }

        $request = Http::acceptJson();
        if ($token !== '') {
            $request = $request->withToken($token);
        }

        $response = $request->post($endpoint, [
            'to' => $phone,
            'message' => $message,
            'sender' => config('services.sms.sender'),
        ]);

        if ($response->failed()) {
            Log::error('OTP SMS delivery failed', ['status' => $response->status()]);

            return false;
        }

        return true;
    }
}
