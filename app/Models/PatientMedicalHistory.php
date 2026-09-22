<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientMedicalHistory extends Model
{
    protected $fillable = [
        'patient_id',
        'conditions',
        'allergies',
        'current_medications',
        'past_surgeries',
        'family_history',
        'lifestyle_notes',
        'recorded_by_id',
    ];

    protected $casts = [
        'conditions' => 'array',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }
}
