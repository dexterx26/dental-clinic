<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DentistSchedule extends Model
{
    protected $fillable = [
        'dentist_id',
        'day_of_week',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'is_available',
        'notes',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'day_of_week' => 'integer',
    ];

    public function dentist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dentist_id');
    }

    public function getDayNameAttribute(): string
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return $days[$this->day_of_week] ?? 'Unknown';
    }
}
