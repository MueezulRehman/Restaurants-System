<?php

namespace App\Models;

use App\Models\Concerns\UsesCentralConnection;
use Illuminate\Database\Eloquent\Model;

class CeoBusinessAssignment extends Model
{
    use UsesCentralConnection;
    protected $fillable = [
        'user_id',
        'restaurant_id',
        'access_level',
        'access_scope',
        'can_view_financials',
        'can_view_staff',
        'can_view_inventory',
        'can_manage_branches',
        'is_active',
    ];

    protected $casts = [
        'can_view_financials' => 'boolean',
        'can_view_staff' => 'boolean',
        'can_view_inventory' => 'boolean',
        'can_manage_branches' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function hasAllBranchAccess(): bool
    {
        return $this->access_scope === 'all_branches';
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
