<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a customer places an online/storefront order.
 * Managers listen on channel: restaurant.{id}.orders
 *
 * @author Mueez Ul Rehman
 */
class NewOrderPlaced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function broadcastOn(): array
    {
        $restaurantId = $this->order->restaurant_id ?? 0;

        return [
            new Channel('restaurant.' . $restaurantId . '.orders'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.placed';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'tracking_token' => $this->order->tracking_token,
            'order_type' => $this->order->order_type,
            'status' => $this->order->status,
            'customer_name' => $this->order->customer_name,
            'customer_phone' => $this->order->customer_phone,
            'total' => (string) $this->order->total,
            'restaurant_id' => $this->order->restaurant_id,
            'created_at' => optional($this->order->created_at)->toIso8601String(),
        ];
    }
}
