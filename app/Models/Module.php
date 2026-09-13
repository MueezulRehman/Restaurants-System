<?php

namespace App\Models;

use App\Models\Concerns\UsesCentralConnection;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use UsesCentralConnection;
    protected $fillable = [
        'name', 'key', 'description', 'icon', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function businessTypes()
    {
        return $this->belongsToMany(
            BusinessType::class,
            'business_type_modules',
            'module_id',
            'business_type_id'
        )->select('business_types.*');
    }
}
