<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicineCategory extends Model
{
    protected $fillable = ['name', 'status'];

    public function medicines()
    {
        return $this->hasMany(Medicine::class, 'category_id');
    }
}
