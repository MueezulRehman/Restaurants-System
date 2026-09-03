<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Broadcast on a PRIVATE channel keyed by the order's tracking_token —
     * not its numeric id. This means only someone who has the unique tracking
     * link (sent to the customer after checkout) can ever subscribe to updates
     * for that specific order. Other customers cannot listen in on it.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('order.' . $this->order->tracking_token),
        ];
    }

    public function broadcastAs(): string
    {
        return 'status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'status' => $this->order->status,
            'status_label' => $this->order->status_label,
            'progress_percent' => $this->order->progress_percent,
            'estimated_minutes' => $this->order->estimated_minutes,
        ];
    }
}
