<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    protected $type;
    protected $customMessage;

    public function __construct(Order $order, string $type, ?string $customMessage = null)
    {
        $this->order = $order;
        $this->type = $type;
        $this->customMessage = $customMessage;
    }

    public function handle(): void
    {
        try {
            // Configuration check
            $whatsappApiUrl = config('services.whatsapp.api_url');
            $whatsappToken = config('services.whatsapp.token');

            if (!$whatsappApiUrl || !$whatsappToken) {
                Log::warning('WhatsApp service not configured');
                return;
            }

            $message = $this->buildMessage();
            $phoneNumber = $this->order->customer_phone;

            // Send via WhatsApp API (placeholder implementation)
            Log::info("WhatsApp notification queued for $phoneNumber: $message");

            // TODO: Integrate actual WhatsApp API call here
            // $response = Http::withToken($whatsappToken)->post($whatsappApiUrl, [...]);
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp notification: {$e->getMessage()}");
            $this->fail($e);
        }
    }

    protected function buildMessage(): string
    {
        return match ($this->type) {
            'order_received' => "Your order #{$this->order->order_number} has been received. Total: Rs. {$this->order->total}",
            'status_update' => $this->customMessage ?? "Order {$this->order->order_number} status updated to {$this->order->status}",
            default => "Order {$this->order->order_number} update",
        };
    }
}
