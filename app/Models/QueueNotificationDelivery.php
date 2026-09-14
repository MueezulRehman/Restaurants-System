<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class QueueNotificationDelivery extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'queue_entry_id',
        'channel',
        'recipient_masked',
        'provider_message_id',
        'status',
        'error',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function queueEntry()
    {
        return $this->belongsTo(QueueEntry::class);
    }
}
