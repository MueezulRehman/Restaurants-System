<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToRestaurant;

class Salary extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id', 'user_id', 'month', 'amount', 'deductions', 'net_paid', 'paid_at', 'paid_by',
    ];

    protected $casts = [
        'month' => 'date',
        'amount' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_paid' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
