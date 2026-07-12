<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'patient_name',
        'medicine_name',
        'notes',
    ];
}
