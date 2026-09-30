<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DentalLabCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_number',
        'patient_id',
        'dentist_id',
        'lab_name',
        'technician_name',
        'appliance_type',
        'tooth_number',
        'shade',
        'sent_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'cost',
        'status',
        'instructions',
        'notes',
        'created_by_id',
    ];

    protected $casts = [
        'sent_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function dentist()
    {
        return $this->belongsTo(User::class, 'dentist_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
