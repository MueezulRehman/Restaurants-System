<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function restaurants()
    {
        return $this->hasMany(Restaurant::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return match ($this->name) {
            'General Business' => 'General Store',
            'Medical Store' => 'Pharmacy',
            default => (string) $this->name,
        };
    }

    public function modules()
    {
        return $this->belongsToMany(
            Module::class,
            'business_type_modules',
            'business_type_id',
            'module_id'
        )->select('modules.*')->orderBy('modules.name');
    }
}
