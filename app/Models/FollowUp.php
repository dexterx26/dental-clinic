<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUp extends Model
{
    protected $fillable = [
        'patient_id',
        'dentist_id',
        'treatment_id',
        'follow_up_type',
        'scheduled_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function dentist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dentist_id');
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }
}
