<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class PatientAllergy extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'patient_id',
        'allergy_name',
        'description',
        'severity',
        'trigger_medicines',
        'is_active',
    ];

    protected $casts = [
        'trigger_medicines' => 'array',
        'is_active' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
