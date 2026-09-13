<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FittingRoomItem extends Model
{
    protected $fillable = ['fitting_room_session_id', 'menu_item_id', 'product_variant_id', 'status', 'quantity'];
    public function session()
    {
        return $this->belongsTo(FittingRoomSession::class, 'fitting_room_session_id');
    }
    public function item()
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
