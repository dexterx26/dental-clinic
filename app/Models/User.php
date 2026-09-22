<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'license_number',
        'specialization',
        'avatar',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Role check helpers
    public function isAdmin(): bool
    {
        return $this->role === 'administrator';
    }

    public function isDentist(): bool
    {
        return $this->role === 'dentist';
    }

    public function isReceptionist(): bool
    {
        return $this->role === 'receptionist';
    }

    public function isAssistant(): bool
    {
        return $this->role === 'dental_assistant';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    public function hasRole(array|string $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        return $this->role === $roles;
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(DentistSchedule::class, 'dentist_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'dentist_id');
    }

    public function examinations(): HasMany
    {
        return $this->hasMany(Examination::class, 'dentist_id');
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class, 'dentist_id');
    }

    public function treatmentPlans(): HasMany
    {
        return $this->hasMany(TreatmentPlan::class, 'dentist_id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'dentist_id');
    }

    public function paymentsReceived(): HasMany
    {
        return $this->hasMany(Payment::class, 'cashier_id');
    }
}
