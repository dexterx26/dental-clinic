<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'service_code',
        'name',
        'category',
        'description',
        'standard_price',
        'duration_minutes',
        'is_active',
    ];

    protected $casts = [
        'standard_price' => 'decimal:2',
        'duration_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
