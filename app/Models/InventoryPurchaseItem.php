<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryPurchaseItem extends Model
{
    protected $fillable = ['inventory_purchase_id', 'menu_item_id', 'product_variant_id', 'quantity', 'purchase_price', 'selling_price', 'line_total'];
    protected $casts = ['quantity' => 'decimal:3', 'purchase_price' => 'decimal:2', 'selling_price' => 'decimal:2', 'line_total' => 'decimal:2'];
    public function purchase()
    {
        return $this->belongsTo(InventoryPurchase::class, 'inventory_purchase_id');
    }
    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
