<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicineUnit extends Model
{
    protected $fillable = ['name', 'status'];

    public function medicines()
    {
        return $this->hasMany(Medicine::class, 'unit_id');
    }
}
