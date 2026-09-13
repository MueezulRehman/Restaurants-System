<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class WorkflowDefinition extends Model
{
    use BelongsToRestaurant;
    protected $fillable = ['restaurant_id', 'entity_type', 'name', 'statuses', 'transitions', 'is_active'];
    protected $casts = ['statuses' => 'array', 'transitions' => 'array', 'is_active' => 'boolean'];
    public function records()
    {
        return $this->hasMany(WorkflowRecord::class);
    }
}
