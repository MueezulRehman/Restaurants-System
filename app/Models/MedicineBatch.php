<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToRestaurant;

class MedicineBatch extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'medicine_id',
        'restaurant_id',
        'batch_number',
        'mfg_date',
        'expiry_date',
        'purchase_price',
        'selling_price',
        'wholesale_price',
        'quantity',
        'rack_number',
        'storage_location',
        'purchase_item_id',
    ];

    protected $casts = [
        'mfg_date' => 'date',
        'expiry_date' => 'date',
        'quantity' => 'decimal:3',
        'selling_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function purchaseItem()
    {
        return $this->belongsTo(PurchaseItem::class, 'purchase_item_id');
    }
}
