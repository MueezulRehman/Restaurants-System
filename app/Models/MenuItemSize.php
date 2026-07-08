<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItemSize extends Model
{
    protected $fillable = ['menu_item_id', 'size_label', 'price', 'sort_order'];

    protected $casts = ['price' => 'decimal:2'];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }
}
