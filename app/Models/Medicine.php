<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToRestaurant;

class Medicine extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id','name','generic_name','brand_id','manufacturer_id','category_id',
        'dosage_form','strength','sku','barcode','requires_prescription','track_stock','min_stock',
        'description','image','tax',
    ];

    protected $casts = [
        'requires_prescription' => 'boolean',
        'track_stock' => 'boolean',
        'tax' => 'decimal:2',
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
}

