<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Examination extends Model
{
    protected $fillable = [
        'examination_number',
        'patient_id',
        'dentist_id',
        'appointment_id',
        'exam_date',
        'chief_complaint',
        'history_of_present_complaint',
        'clinical_findings',
        'diagnosis',
        'treatment_recommendations',
        'oral_exam_findings',
        'clinical_notes',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'oral_exam_findings' => 'array',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function dentist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dentist_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
