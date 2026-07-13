<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagerFeedback extends Model
{
    protected $table = 'manager_feedbacks';

    protected $fillable = [
        'restaurant_id', 'user_id', 'type', 'title', 'message',
        'status', 'admin_reply', 'replied_at', 'resolved_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
