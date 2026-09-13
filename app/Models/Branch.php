<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use BelongsToRestaurant;

    protected $fillable = ['restaurant_id', 'name', 'code', 'address', 'phone', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
    public function outgoingTransfers()
    {
        return $this->hasMany(StockTransfer::class, 'from_branch_id');
    }
    public function incomingTransfers()
    {
        return $this->hasMany(StockTransfer::class, 'to_branch_id');
    }
}
