<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WholesalePriceListItem extends Model
{
    protected $fillable = ['wholesale_price_list_id', 'product_variant_id', 'menu_item_id', 'price'];
    protected $casts = ['price' => 'decimal:2'];
    public function priceList()
    {
        return $this->belongsTo(WholesalePriceList::class, 'wholesale_price_list_id');
    }
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }
}
