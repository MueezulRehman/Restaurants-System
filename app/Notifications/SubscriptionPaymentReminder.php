<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * @author Mueez Ul Rehman
 */
class SubscriptionPaymentReminder extends Notification
{
    use Queueable;

    public function __construct(
        public string $message,
        public string $businessName
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Codeibex subscription payment reminder — ' . $this->businessName)
            ->line($this->message)
            ->line('Open your manager Subscription page for bank details.')
            ->action('Open subscription', url('/manager/subscription'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_payment_reminder',
            'business' => $this->businessName,
            'message' => $this->message,
        ];
    }
}
