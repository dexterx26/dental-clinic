<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TreatmentPlanItem extends Model
{
    protected $fillable = [
        'treatment_plan_id',
        'service_id',
        'procedure_name',
        'tooth_number',
        'surface',
        'estimated_cost',
        'priority',
        'sessions_required',
        'status',
        'notes',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'sessions_required' => 'integer',
    ];

    public function treatmentPlan(): BelongsTo
    {
        return $this->belongsTo(TreatmentPlan::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function treatment(): HasOne
    {
        return $this->hasOne(Treatment::class);
    }
}
