<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class ServicePackagePurchase extends Model
{
    use BelongsToRestaurant;
    protected $fillable = ['restaurant_id', 'service_package_id', 'customer_id', 'starts_at', 'ends_at', 'remaining_visits', 'amount_paid', 'status'];
    protected $casts = ['starts_at' => 'date', 'ends_at' => 'date', 'remaining_visits' => 'integer', 'amount_paid' => 'decimal:2'];
    public function package()
    {
        return $this->belongsTo(ServicePackage::class, 'service_package_id');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
