<?php

namespace App\Services;

use App\Models\OtpVerification;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class OtpService
{
    public function issue(Restaurant $restaurant, string $phone, array $channels = ['sms']): OtpVerification
    {
        $phone = $this->normalizePhone($phone);
        $channels = array_values(array_intersect($channels, ['sms', 'whatsapp'])) ?: ['sms'];
        $code = (string) random_int(100000, 999999);

        OtpVerification::where('restaurant_id', $restaurant->id)
            ->where('phone', $phone)
            ->where('purpose', 'guest_order')
            ->whereNull('verified_at')
            ->update(['expires_at' => now()]);

        $verification = OtpVerification::create([
            'restaurant_id' => $restaurant->id,
            'phone' => $phone,
            'code_hash' => Hash::make($code),
            'purpose' => 'guest_order',
            'channels' => $channels,
            'attempts' => 0,
            'expires_at' => now()->addMinutes(5),
        ]);

        $businessName = (string) $restaurant->name;
        $sent = false;
        if (in_array('sms', $channels, true)) {
            $sent = app(SmsService::class)->sendOtp($phone, $code, $businessName) || $sent;
        }
        if (in_array('whatsapp', $channels, true)) {
            $sent = app(WhatsAppService::class)->sendOtp($phone, $code, $businessName) || $sent;
        }

        if (! $sent) {
            Log::warning('No OTP provider delivered the guest order code.', [
                'restaurant_id' => $restaurant->id,
                'channels' => $channels,
            ]);
        }

        return $verification;
    }

    public function verify(Restaurant $restaurant, string $phone, string $code): bool
    {
        $phone = $this->normalizePhone($phone);
        $verification = OtpVerification::where('restaurant_id', $restaurant->id)
            ->where('phone', $phone)
            ->where('purpose', 'guest_order')
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (! $verification || $verification->expires_at->isPast() || $verification->attempts >= 5) {
            return false;
        }

        $verification->increment('attempts');
        if (! Hash::check($code, $verification->code_hash)) {
            return false;
        }

        $verification->forceFill(['verified_at' => now()])->save();

        return true;
    }

    public function normalizePhone(string $phone): string
    {
        $countryCode = (string) config('services.whatsapp.default_country_code', '92');
        $digits = preg_replace('/\D/', '', $phone);
        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            $digits = $countryCode . substr($digits, 1);
        } elseif (! str_starts_with($digits, $countryCode)) {
            $digits = $countryCode . $digits;
        }

        return '+' . $digits;
    }
}
