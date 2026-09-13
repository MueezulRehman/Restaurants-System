<?php

namespace App\Models;

use App\Models\Concerns\UsesCentralConnection;
use Illuminate\Database\Eloquent\Model;

class CeoBranchAssignment extends Model
{
    use UsesCentralConnection;
    protected $fillable = [
        'user_id',
        'restaurant_id',
        'branch_id',
        'access_level',
        'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
