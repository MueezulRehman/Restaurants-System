<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToRestaurant;

class Cashbook extends Model
{
    use BelongsToRestaurant;

    protected $table = 'cashbook';

    protected $fillable = [
        'restaurant_id', 'type', 'amount', 'description', 'source', 'order_id', 'date', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
