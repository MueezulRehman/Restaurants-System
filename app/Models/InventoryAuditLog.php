<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryAuditLog extends Model
{
    protected $fillable = [
        'restaurant_id',
        'item_type',
        'item_id',
        'action',
        'before_values',
        'after_values',
        'user_id',
        'reason',
    ];

    protected $casts = [
        'before_values' => 'json',
        'after_values' => 'json',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an inventory change
     */
    public static function log($restaurantId, $itemType, $itemId, $action, $beforeValues = null, $afterValues = null, $userId = null, $reason = null)
    {
        return static::create([
            'restaurant_id' => $restaurantId,
            'item_type' => $itemType,
            'item_id' => $itemId,
            'action' => $action,
            'before_values' => $beforeValues,
            'after_values' => $afterValues,
            'user_id' => $userId ?? auth()->id(),
            'reason' => $reason,
        ]);
    }
}
