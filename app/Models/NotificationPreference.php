<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    protected $fillable = [
        'notifiable_type', 'notifiable_id', 'email_enabled', 'push_enabled',
        'whatsapp_enabled', 'low_stock_alerts', 'order_updates', 'feedback_replies',
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'push_enabled' => 'boolean',
        'whatsapp_enabled' => 'boolean',
        'low_stock_alerts' => 'boolean',
        'order_updates' => 'boolean',
        'feedback_replies' => 'boolean',
    ];

    public function notifiable()
    {
        return $this->morphTo();
    }
}
