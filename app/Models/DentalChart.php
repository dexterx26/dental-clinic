<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DentalChart extends Model
{
    protected $fillable = [
        'patient_id',
        'tooth_number',
        'surface',
        'condition',
        'notes',
        'color',
        'updated_by_id',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }
}
