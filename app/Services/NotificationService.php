<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Models\Customer;
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
    ): Notification {
        // Create notification record
        $notification = Notification::create([
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
    public static function sendEmail(Notification $notification, $email): bool
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
    public static function sendWhatsApp(Notification $notification, $phoneNumber): bool
    {
        try {
            // Placeholder: integrate with WhatsApp API
            // For now, just log it
            Log::info("WhatsApp notification queued for {$phoneNumber}: {$notification->title}");

            // TODO: Implement actual WhatsApp sending via Twilio or similar
            // $client = new Twilio\Rest\Client($accountSid, $authToken);
            // $client->messages->create($phoneNumber, ...);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp notification: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Send browser push notification.
     * TODO: Integrate with web push service
     */
    public static function sendPush(Notification $notification, $subscriber): bool
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
