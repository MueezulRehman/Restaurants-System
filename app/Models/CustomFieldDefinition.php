<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class CustomFieldDefinition extends Model
{
    use BelongsToRestaurant;
    protected $fillable = ['restaurant_id', 'entity_type', 'name', 'field_key', 'field_type', 'options', 'is_required', 'is_active'];
    protected $casts = ['options' => 'array', 'is_required' => 'boolean', 'is_active' => 'boolean'];
    public function values()
    {
        return $this->hasMany(CustomFieldValue::class);
    }
}
