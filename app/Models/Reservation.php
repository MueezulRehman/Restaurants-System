<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use BelongsToRestaurant;

    protected $fillable = ['restaurant_id', 'customer_id', 'table_id', 'guest_name', 'guest_phone', 'party_size', 'starts_at', 'ends_at', 'status', 'notes'];
    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'party_size' => 'integer'];
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function table()
    {
        return $this->belongsTo(Table::class);
    }
}
