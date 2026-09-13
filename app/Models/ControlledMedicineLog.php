<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class ControlledMedicineLog extends Model
{
    use BelongsToRestaurant;
    protected $fillable = ['restaurant_id', 'medicine_id', 'customer_id', 'prescription_id', 'dispensed_by', 'quantity', 'witness_name', 'reason', 'dispensed_at'];
    protected $casts = ['quantity' => 'decimal:3', 'dispensed_at' => 'datetime'];
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }
    public function dispenser()
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }
}
