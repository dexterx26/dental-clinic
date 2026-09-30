<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsentForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'consent_number',
        'patient_id',
        'dentist_id',
        'treatment_id',
        'consent_type',
        'title',
        'description_and_risks',
        'patient_signature',
        'signed_at',
        'witness_name',
        'witness_signature',
        'status',
        'notes',
        'created_by_id',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function dentist()
    {
        return $this->belongsTo(User::class, 'dentist_id');
    }

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
