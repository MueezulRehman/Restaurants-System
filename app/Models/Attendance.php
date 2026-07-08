<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToRestaurant;

class Attendance extends Model
{
    use BelongsToRestaurant;

    protected $table = 'attendance';

    protected $fillable = ['restaurant_id', 'user_id', 'date', 'status', 'check_in', 'check_out', 'notes'];

    protected $casts = ['date' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
