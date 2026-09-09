<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class InventoryPurchase extends Model
{
    use BelongsToRestaurant;
    protected $fillable = ['restaurant_id', 'supplier_id', 'supplier_name', 'invoice_no', 'purchase_date', 'total', 'status', 'created_by', 'notes'];
    protected $casts = ['purchase_date' => 'date', 'total' => 'decimal:2'];
    public function items()
    {
        return $this->hasMany(InventoryPurchaseItem::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
