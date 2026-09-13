<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowRecord extends Model
{
    protected $fillable = ['workflow_definition_id', 'entity_id', 'status', 'data'];
    protected $casts = ['data' => 'array'];
    public function definition()
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
    }
}
