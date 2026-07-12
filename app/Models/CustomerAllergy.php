<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAllergy extends Model
{
    protected $fillable = [
        'customer_id',
        'allergy_name',
        'description',
        'severity',
        'trigger_medicines',
        'is_active',
    ];

    protected $casts = [
        'trigger_medicines' => 'json',
        'is_active' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function triggerMedicines()
    {
        if (!$this->trigger_medicines) {
            return collect();
        }
        
        return Medicine::whereIn('id', $this->trigger_medicines)->get();
    }
}
