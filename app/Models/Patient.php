<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_number',
        'first_name',
        'middle_name',
        'last_name',
        'dob',
        'age',
        'gender',
        'civil_status',
        'nationality',
        'occupation',
        'phone',
        'email',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'preferred_dentist_id',
        'referral_source',
        'photo',
        'status',
        'privacy_consent_accepted',
        'privacy_consent_date',
        'registration_date',
        'notes',
    ];

    protected $casts = [
        'dob' => 'date',
        'registration_date' => 'date',
        'privacy_consent_accepted' => 'boolean',
        'privacy_consent_date' => 'datetime',
        'age' => 'integer',
    ];

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function getComputedAgeAttribute(): int
    {
        return $this->dob ? Carbon::parse($this->dob)->age : ($this->age ?? 0);
    }

    public function preferredDentist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'preferred_dentist_id');
    }

    public function medicalHistory(): HasOne
    {
        return $this->hasOne(PatientMedicalHistory::class, 'patient_id');
    }

    public function dentalHistory(): HasOne
    {
        return $this->hasOne(PatientDentalHistory::class, 'patient_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patient_id')->orderBy('appointment_date', 'desc')->orderBy('start_time', 'desc');
    }

    public function dentalCharts(): HasMany
    {
        return $this->hasMany(DentalChart::class, 'patient_id');
    }

    public function dentalChartHistories(): HasMany
    {
        return $this->hasMany(DentalChartHistory::class, 'patient_id')->orderBy('created_at', 'desc');
    }

    public function examinations(): HasMany
    {
        return $this->hasMany(Examination::class, 'patient_id')->orderBy('exam_date', 'desc');
    }

    public function treatmentPlans(): HasMany
    {
        return $this->hasMany(TreatmentPlan::class, 'patient_id')->orderBy('created_at', 'desc');
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class, 'patient_id')->orderBy('created_at', 'desc');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'patient_id')->orderBy('prescription_date', 'desc');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PatientDocument::class, 'patient_id')->orderBy('created_at', 'desc');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'patient_id')->orderBy('invoice_date', 'desc');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'patient_id')->orderBy('payment_date', 'desc');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'patient_id')->orderBy('scheduled_date', 'asc');
    }

    public function labCases(): HasMany
    {
        return $this->hasMany(DentalLabCase::class, 'patient_id')->orderBy('created_at', 'desc');
    }

    public function consentForms(): HasMany
    {
        return $this->hasMany(ConsentForm::class, 'patient_id')->orderBy('signed_at', 'desc');
    }
}

