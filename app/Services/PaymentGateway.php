<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentGateway
{
    public static function charge(string $method, float $amount, array $payload = []): array
    {
        if ($amount <= 0) {
            return [
                'success' => false,
                'message' => 'Invalid payment amount.',
            ];
        }

        return match ($method) {
            'manual' => self::manual($amount, $payload),
            'stripe' => self::stripe($amount, $payload),
            'jazzcash' => self::genericGateway($amount, 'jazzcash', $payload),
            'easypaisa' => self::genericGateway($amount, 'easypaisa', $payload),
            default => [
                'success' => false,
                'message' => "Unsupported payment method: {$method}",
            ],
        };
    }

    protected static function manual(float $amount, array $payload): array
    {
        return [
            'success' => true,
            'status' => 'pending',
            'transaction_id' => null,
            'message' => 'Manual payment request created. Please confirm payment with the platform administrator.',
        ];
    }

    protected static function stripe(float $amount, array $payload): array
    {
        $secret = config('services.stripe.secret');
        if (! $secret) {
            return [
                'success' => false,
                'message' => 'Stripe is not configured.',
            ];
        }

        $response = Http::withBasicAuth($secret, '')
            ->asForm()
            ->post('https://api.stripe.com/v1/payment_intents', array_filter([
                'amount' => (int) round($amount * 100),
                'currency' => $payload['currency'] ?? config('services.stripe.currency', 'usd'),
                'payment_method_types[]' => 'card',
                'description' => $payload['description'] ?? 'TasteHut subscription payment',
                'payment_method' => $payload['payment_method_token'] ?? null,
                'confirm' => isset($payload['confirm']) ? (bool) $payload['confirm'] : true,
            ], fn ($value) => $value !== null && $value !== false));

        if (! $response->successful()) {
            Log::warning('Stripe payment failed', ['response' => $response->body()]);

            return [
                'success' => false,
                'message' => $response->json('error.message', 'Stripe payment failed'),
            ];
        }

        $body = $response->json();
        $status = $body['status'] ?? 'unknown';

        return [
            'success' => in_array($status, ['succeeded', 'requires_capture', 'requires_confirmation'], true),
            'status' => $status,
            'transaction_id' => $body['id'] ?? null,
            'message' => $status === 'succeeded' ? 'Stripe payment completed successfully.' : 'Stripe payment created; further action may be required.',
        ];
    }

    protected static function genericGateway(float $amount, string $provider, array $payload): array
    {
        $endpoint = config("services.{$provider}.endpoint");
        $token = config("services.{$provider}.token");

        if (! $endpoint || ! $token) {
            return [
                'success' => false,
                'message' => ucfirst($provider) . ' is not configured.',
            ];
        }

        $response = Http::withToken($token)
            ->post($endpoint, array_merge([
                'amount' => $amount,
                'currency' => $payload['currency'] ?? 'PKR',
                'description' => $payload['description'] ?? 'TasteHut subscription payment',
            ], $payload));

        if (! $response->successful()) {
            Log::warning("{$provider} payment request failed", ['response' => $response->body()]);

            return [
                'success' => false,
                'message' => $response->json('message', ucfirst($provider) . ' payment failed'),
            ];
        }

        $body = $response->json();

        return [
            'success' => $body['success'] ?? true,
            'status' => $body['status'] ?? 'pending',
            'transaction_id' => $body['transaction_id'] ?? $body['id'] ?? null,
            'message' => $body['message'] ?? ucfirst($provider) . ' payment created.',
        ];
    }
}
