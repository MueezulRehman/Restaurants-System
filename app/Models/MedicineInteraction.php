<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicineInteraction extends Model
{
    protected $fillable = [
        'medicine_id_1',
        'medicine_id_2',
        'interaction_type',
        'interaction_description',
        'recommended_action',
        'source',
    ];

    public function medicineFirst()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id_1');
    }

    public function medicineSecond()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id_2');
    }

    /**
     * Find all interactions for a medicine (both directions)
     */
    public static function getInteractionsFor($medicineId)
    {
        return static::where('medicine_id_1', $medicineId)
            ->orWhere('medicine_id_2', $medicineId)
            ->get();
    }

    /**
     * Check if two medicines interact
     */
    public static function hasInteraction($medicineId1, $medicineId2)
    {
        return static::where(function ($q) use ($medicineId1, $medicineId2) {
            $q->where('medicine_id_1', $medicineId1)->where('medicine_id_2', $medicineId2);
        })->orWhere(function ($q) use ($medicineId1, $medicineId2) {
            $q->where('medicine_id_1', $medicineId2)->where('medicine_id_2', $medicineId1);
        })->exists();
    }
}
