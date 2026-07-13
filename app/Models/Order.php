<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Support\Str;

class Order extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id', 'customer_id',
        'order_number', 'invoice_number', 'tracking_token', 'order_type', 'channel', 'cashier_id', 'table_number', 'status',
        'customer_name', 'customer_phone', 'address',
        'subtotal', 'delivery_fee', 'total', 'amount_received', 'change_amount', 'payment_method',
        'notes', 'estimated_minutes', 'confirmed_at', 'ready_at', 'delivered_at',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_received' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'ready_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // Statuses in customer-visible order, used to build the progress bar
    public const STATUS_FLOW = ['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered'];

    protected static function boot()
    {
        parent::boot();

        // Every order automatically gets a unique order number and a private
        // tracking token (UUID) the moment it's created. The token — NOT the
        // order id — is what's used in the public tracking URL, so guessing
        // sequential IDs (1, 2, 3...) can never expose someone else's order.
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'TH-' . now()->format('Ymd') . '-' . str_pad(
                    (static::where('restaurant_id', $order->restaurant_id)->whereDate('created_at', now())->count() + 1),
                    4, '0', STR_PAD_LEFT
                );
            }
            if (empty($order->invoice_number)) {
                $order->invoice_number = 'INV-' . now()->format('Ymd') . '-' . str_pad(
                    (static::where('restaurant_id', $order->restaurant_id ?? 0)->whereDate('created_at', now())->count() + 1),
                    4, '0', STR_PAD_LEFT
                );
            }
            if (empty($order->tracking_token)) {
                $order->tracking_token = (string) Str::uuid();
            }
        });
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    // % progress for the visual progress bar on the tracking page
    public function getProgressPercentAttribute(): int
    {
        if ($this->status === 'cancelled') {
            return 0;
        }
        $flow = $this->order_type === 'delivery'
            ? self::STATUS_FLOW
            : array_diff(self::STATUS_FLOW, ['out_for_delivery']);
        $flow = array_values($flow);
        $index = array_search($this->status, $flow);
        if ($index === false) {
            return 0;
        }
        return (int) round((($index + 1) / count($flow)) * 100);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Order received',
            'confirmed' => 'Confirmed',
            'preparing' => 'Being prepared',
            'ready' => 'Ready',
            'out_for_delivery' => 'Out for delivery',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            default => $this->status,
        };
    }
}
