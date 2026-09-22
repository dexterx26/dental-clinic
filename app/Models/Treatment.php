<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Treatment extends Model
{
    protected $fillable = [
        'treatment_number',
        'patient_id',
        'dentist_id',
        'appointment_id',
        'treatment_plan_item_id',
        'procedure_name',
        'tooth_number',
        'surface',
        'diagnosis',
        'procedure_notes',
        'materials_used',
        'anesthesia',
        'complications',
        'follow_up_instructions',
        'cost',
        'payment_status',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
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

    public function treatmentPlanItem(): BelongsTo
    {
        return $this->belongsTo(TreatmentPlanItem::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PatientDocument::class);
    }
}
