<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreatmentPlan extends Model
{
    protected $fillable = [
        'plan_number',
        'patient_id',
        'dentist_id',
        'title',
        'diagnosis',
        'total_estimated_cost',
        'status',
        'patient_decision',
        'decision_date',
        'notes',
    ];

    protected $casts = [
        'total_estimated_cost' => 'decimal:2',
        'decision_date' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function dentist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dentist_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TreatmentPlanItem::class);
    }
}
