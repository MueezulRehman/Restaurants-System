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
        'balance',
        'last_reminder_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'balance' => 'decimal:2',
        'last_reminder_at' => 'datetime',
    ];

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

    public function balanceTransactions()
    {
        return $this->hasMany(CustomerBalanceTransaction::class)->latest();
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function recordBalanceChange(float $amount, string $description, array $options = []): CustomerBalanceTransaction
    {
        $amount = round($amount, 2);
        $type = $options['type'] ?? ($amount >= 0 ? 'charge' : 'payment');

        if ($type === 'payment') {
            $this->balance = max(0, round((float) $this->balance - $amount, 2));
        } else {
            $this->balance = round((float) $this->balance + $amount, 2);
        }

        $this->save();

        return $this->balanceTransactions()->create([
            'restaurant_id' => $options['restaurant_id'] ?? $this->restaurant_id,
            'order_id' => $options['order_id'] ?? null,
            'created_by' => $options['created_by'] ?? null,
            'type' => $type,
            'amount' => abs($amount),
            'source' => $options['source'] ?? 'pos',
            'description' => $description,
        ]);
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
