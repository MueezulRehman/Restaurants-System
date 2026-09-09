<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class FollowUpReminder extends Model
{
    use BelongsToRestaurant;

    protected $fillable = ['restaurant_id', 'customer_id', 'medical_record_id', 'due_at', 'note', 'status', 'completed_at', 'created_by'];
    protected $casts = ['due_at' => 'datetime', 'completed_at' => 'datetime'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }
}
