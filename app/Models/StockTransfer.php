<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    use BelongsToRestaurant;
    protected $fillable = ['restaurant_id', 'from_location', 'to_location', 'item_name', 'quantity', 'status', 'created_by'];
    protected $casts = ['quantity' => 'decimal:3'];
}
