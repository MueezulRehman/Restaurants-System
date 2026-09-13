<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class QueueCounter extends Model
{
    use BelongsToRestaurant;

    protected $fillable = ['restaurant_id', 'doctor_id', 'queue_date', 'last_issued_number'];

    protected $casts = ['queue_date' => 'date'];

    public function doctor() { return $this->belongsTo(Doctor::class); }
}
