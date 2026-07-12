<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class PurchaseHeader extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id', 'supplier_name', 'invoice_no', 'purchase_date', 'total',
        'status', 'created_by', 'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
