<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class NursingAssignment extends Model
{
    use BelongsToRestaurant;

    protected $fillable = ['restaurant_id', 'hospital_admission_id', 'nurse_id', 'assigned_at', 'status', 'notes'];

    protected $casts = ['assigned_at' => 'datetime'];

    public function admission() { return $this->belongsTo(HospitalAdmission::class, 'hospital_admission_id'); }
    public function nurse() { return $this->belongsTo(User::class, 'nurse_id'); }
}
