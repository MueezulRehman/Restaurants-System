<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariantAttributeValue extends Model
{
    protected $fillable = [
        'variant_attribute_id', 'product_variant_id', 'value', 'sort_order',
    ];

    public function attribute()
    {
        return $this->belongsTo(VariantAttribute::class, 'variant_attribute_id');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
