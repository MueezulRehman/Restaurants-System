<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BatchRecall extends Model
{
    use SoftDeletes;

    protected $table = 'batch_recalls';

    protected $fillable = [
        'restaurant_id',
        'medicine_id',
        'medicine_batch_id',
        'recall_number',
        'reason',
        'description',
        'recall_date',
        'quantity_recalled',
        'status',
        'action_taken',
        'issued_by',
    ];

    protected $casts = [
        'recall_date' => 'date',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function batch()
    {
        return $this->belongsTo(MedicineBatch::class, 'medicine_batch_id');
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
