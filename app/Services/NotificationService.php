<?php

namespace App\Services;

use App\Models\PlatformNotification;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Models\Customer;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send notification across multiple channels.
     */
    public static function send(
        $restaurantId,
        $type,
        $title,
        $message,
        $channels = ['email'],
        $user = null,
        $customer = null
    ): PlatformNotification {
        // Create notification record
        $notification = PlatformNotification::create([
            'restaurant_id' => $restaurantId,
            'user_id' => $user?->id,
            'customer_id' => $customer?->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'channels' => $channels,
            'status' => 'pending',
        ]);

        // Send via each channel asynchronously
        foreach ($channels as $channel) {
            dispatch(new \App\Jobs\SendNotification($notification, $channel));
        }

        return $notification;
    }

    /**
     * Send email notification.
     */
    public static function sendEmail(PlatformNotification $notification, $email): bool
    {
        try {
            Mail::raw($notification->message, function ($message) use ($email, $notification) {
                $message->to($email)
                    ->subject($notification->title);
            });

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send email notification: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Send WhatsApp notification.
     * TODO: Integrate with WhatsApp API (Twilio, Meta, etc.)
     */
    public static function sendWhatsApp(PlatformNotification $notification, $phoneNumber): bool
    {
        try {
            $phone = self::normalizePhoneNumber($phoneNumber);
            $message = trim($notification->message);

            if (empty($phone) || empty($message)) {
                return false;
            }

            $whatsappService = new WhatsAppService();
            $response = $whatsappService->sendText($phone, $message);

            return $response !== false;
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp notification: {$e->getMessage()}");
            return false;
        }
    }

    protected static function normalizePhoneNumber($phoneNumber): string
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

    /**
     * Send browser push notification.
     * TODO: Integrate with web push service
     */
    public static function sendPush(PlatformNotification $notification, $subscriber): bool
    {
        try {
            $subscriptions = $subscriber->pushSubscriptions;

            foreach ($subscriptions as $subscription) {
                // TODO: Send to push service (Firebase Cloud Messaging, OneSignal, etc.)
                Log::info("Push notification queued for subscriber: {$notification->title}");
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send push notification: {$e->getMessage()}");
            return false;
        }
    }

    public static function notifyLowStock($restaurant, $variant): void
    {
        self::send(
            $restaurant->id,
            'low_stock',
            'Low Stock Alert',
            "Product variant {$variant->variant_name} (SKU: {$variant->sku}) is running low. Current stock: {$variant->quantity_available}",
            ['email'],
            null,
            null
        );
    }

    /**
     * Check user's notification preferences.
     */
    public static function isChannelEnabled($user, string $channel): bool
    {
        $preference = NotificationPreference::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->first();

        if (!$preference) {
            // Create default preferences if none exist
            $preference = NotificationPreference::create([
                'notifiable_type' => get_class($user),
                'notifiable_id' => $user->id,
            ]);
        }

        return match ($channel) {
            'email' => $preference->email_enabled,
            'push' => $preference->push_enabled,
            'whatsapp' => $preference->whatsapp_enabled,
            default => false,
        };
    }

    /**
     * Send low-stock alert notification.
     */
    public static function sendLowStockAlert($restaurant, $variant): void
    {
        $managers = User::where('restaurant_id', $restaurant->id)
            ->where('role', 'manager')
            ->get();

        foreach ($managers as $manager) {
            if (self::isChannelEnabled($manager, 'email')) {
                self::send(
                    $restaurant->id,
                    'low_stock',
                    'Low Stock Alert',
                    "Product variant {$variant->variant_name} (SKU: {$variant->sku}) is running low. Current stock: {$variant->quantity_available}",
                    ['email'],
                    $manager
                );
            }
        }
    }
}
