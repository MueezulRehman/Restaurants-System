<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class CommissionRule extends Model
{
    use BelongsToRestaurant;

    protected $fillable = ['restaurant_id', 'staff_id', 'type', 'value', 'is_active'];
    protected $casts = ['value' => 'decimal:2', 'is_active' => 'boolean'];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
