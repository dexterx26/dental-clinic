<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientDentalHistory extends Model
{
    protected $fillable = [
        'patient_id',
        'previous_dentist',
        'last_dental_visit',
        'past_treatments',
        'notes',
    ];

    protected $casts = [
        'last_dental_visit' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
