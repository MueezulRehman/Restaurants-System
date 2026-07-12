<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'restaurant_id',
        'name', 
        'phone', 
        'email', 
        'password', 
        'default_address',
    ];

    protected $hidden = ['password', 'remember_token'];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function allergies()
    {
        return $this->hasMany(CustomerAllergy::class);
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    /**
     * Get active allergies
     */
    public function getActiveAllergies()
    {
        return $this->allergies()->where('is_active', true)->get();
    }

    /**
     * Check if customer is allergic to a medicine
     */
    public function isAllergicToMedicine($medicineId)
    {
        return $this->allergies()
            ->where('is_active', true)
            ->whereJsonContains('trigger_medicines', $medicineId)
            ->exists();
    }

    /**
     * Get allergy warnings for a medicine
     */
    public function getAllergyWarningsForMedicine($medicineId)
    {
        return $this->allergies()
            ->where('is_active', true)
            ->whereJsonContains('trigger_medicines', $medicineId)
            ->get();
    }
}
