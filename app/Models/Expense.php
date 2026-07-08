<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToRestaurant;

class Expense extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id', 'category', 'amount', 'description', 'date', 'receipt_image', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
