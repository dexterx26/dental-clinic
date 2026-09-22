<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    protected $fillable = [
        'appointment_number',
        'patient_id',
        'dentist_id',
        'service_id',
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'queue_number',
        'checked_in_at',
        'reason',
        'notes',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'checked_in_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function dentist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dentist_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function examination(): HasOne
    {
        return $this->hasOne(Examination::class);
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class);
    }

    public function prescription(): HasOne
    {
        return $this->hasOne(Prescription::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
