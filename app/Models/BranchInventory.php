<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class BranchInventory extends Model
{
    use BelongsToRestaurant;

    protected $fillable = ['restaurant_id', 'branch_id', 'item_type', 'item_id', 'quantity'];
    protected $casts = ['quantity' => 'decimal:3'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function item()
    {
        return match ($this->item_type) {
            'menu_item' => $this->belongsTo(MenuItem::class, 'item_id'),
            'variant' => $this->belongsTo(ProductVariant::class, 'item_id'),
            default => $this->belongsTo(MenuItem::class, 'item_id'),
        };
    }
}
