<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manufacturer extends Model
{
    protected $fillable = ['name', 'status'];

    public function medicines()
    {
        return $this->hasMany(Medicine::class, 'manufacturer_id');
    }
}
