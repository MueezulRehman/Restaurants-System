<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id', 'medicine_id', 'batch_no', 'manufacture_date', 'expiry_date',
        'purchase_price', 'selling_price', 'qty', 'free_qty', 'tax', 'discount',
        'rack', 'line_total',
    ];

    protected $casts = [
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(PurchaseHeader::class, 'purchase_id');
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function batch()
    {
        return $this->hasOne(MedicineBatch::class, 'purchase_item_id');
    }
}
