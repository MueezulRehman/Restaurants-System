<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToRestaurant;

class Medicine extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'name',
        'generic_name',
        'brand_id',
        'manufacturer_id',
        'category_id',
        'dosage_form',
        'strength',
        'sku',
        'barcode',
        'requires_prescription',
        'track_stock',
        'min_stock',
        'description',
        'image',
        'tax',
        'unit_type',
        'allow_fractional_qty',
        'price_per_unit',
    ];

    protected $casts = [
        'requires_prescription' => 'boolean',
        'track_stock' => 'boolean',
        'tax' => 'decimal:2',
        'allow_fractional_qty' => 'boolean',
        'price_per_unit' => 'decimal:2',
    ];

    public function batches()
    {
        return $this->hasMany(MedicineBatch::class);
    }

    public function category()
    {
        return $this->belongsTo(MedicineCategory::class, 'category_id');
    }

    public function interactionsAsFirst()
    {
        return $this->hasMany(MedicineInteraction::class, 'medicine_id_1');
    }

    public function interactionsAsSecond()
    {
        return $this->hasMany(MedicineInteraction::class, 'medicine_id_2');
    }

    /**
     * Get all interactions for this medicine
     */
    public function getAllInteractions()
    {
        return MedicineInteraction::getInteractionsFor($this->id);
    }

    /**
     * Get stock quantity across all batches
     */
    public function getTotalStock()
    {
        return $this->batches()->sum('quantity');
    }

    public function effectiveUnitPrice(): float
    {
        if ($this->price_per_unit !== null) {
            return (float) $this->price_per_unit;
        }
        // fallback to first batch selling price if available
        $first = $this->batches()->orderBy('expiry_date')->first();
        if ($first) return (float) $first->selling_price;
        return 0.0;
    }

    public function unitLabel(): string
    {
        $type = $this->unit_type ?: 'kg';
        return match (strtolower((string) $type)) {
            'kg', 'kilogram' => 'kg',
            'g', 'gram' => 'g',
            'liter', 'litre', 'l' => 'L',
            'dozen' => 'dozen',
            'piece', 'pcs', 'pc' => 'pc',
            default => (string) $type,
        };
    }

    public function allowsFractionalQty(): bool
    {
        if ($this->allow_fractional_qty) return true;
        $type = strtolower((string) ($this->unit_type ?: 'kg'));
        return in_array($type, ['kg', 'kilogram', 'g', 'gram', 'liter', 'litre', 'l'], true);
    }
}
