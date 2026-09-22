<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientDocument extends Model
{
    protected $fillable = [
        'patient_id',
        'treatment_id',
        'tooth_number',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'category',
        'description',
        'uploaded_by_id',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }
}
