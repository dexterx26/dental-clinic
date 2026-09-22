<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DentalChartHistory extends Model
{
    protected $fillable = [
        'patient_id',
        'tooth_number',
        'surface',
        'previous_condition',
        'new_condition',
        'notes',
        'changed_by_id',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_id');
    }
}
