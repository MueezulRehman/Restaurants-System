<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Support\Carbon;

class Deal extends Model
{
    use BelongsToRestaurant;

    protected $fillable = ['restaurant_id', 'name', 'deal_number', 'price', 'start_date', 'end_date', 'description', 'image', 'is_active'];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString());
            })
            ->orderBy('deal_number');
    }

    public function isActiveNow(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->start_date && Carbon::parse($this->start_date)->startOfDay()->greaterThan(now()->startOfDay())) {
            return false;
        }

        if ($this->end_date && Carbon::parse($this->end_date)->endOfDay()->lessThan(now()->endOfDay())) {
            return false;
        }

        return true;
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
