<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class ServiceCase extends Model
{
    use BelongsToRestaurant;
    protected $fillable = ['restaurant_id', 'customer_id', 'menu_item_id', 'case_type', 'serial_number', 'status', 'title', 'description', 'resolution', 'estimated_cost', 'received_at', 'due_at', 'completed_at', 'assigned_to', 'created_by'];
    protected $casts = ['estimated_cost' => 'decimal:2', 'received_at' => 'date', 'due_at' => 'date', 'completed_at' => 'date'];
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }
}
