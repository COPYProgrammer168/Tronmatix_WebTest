<?php

// app/Models/DeliverySchedule.php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DeliverySchedule extends Model
{
    protected $fillable = [
        'day_of_week',   // 0 = Sunday … 6 = Saturday
        'time_start',    // 'HH:MM' e.g. '08:00'
        'time_end',      // 'HH:MM' e.g. '12:00'
        'is_available',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_available' => 'boolean',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeAvailable(Builder $q): Builder
    {
        return $q->where('is_available', true);
    }

    // FIX [1]: scopeForDay — checkout picker queries available slots by day
    public function scopeForDay(Builder $q, int $dayOfWeek): Builder
    {
        return $q->where('day_of_week', $dayOfWeek)->where('is_available', true);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getDayNameAttribute(): string
    {
        return ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$this->day_of_week] ?? 'Unknown';
    }

    /** Slot label: "08:00 – 12:00" */
    public function getSlotLabelAttribute(): string
    {
        return "{$this->time_start} – {$this->time_end}";
    }

    /** Full label: "Monday: 08:00 – 12:00" */
    public function getFullLabelAttribute(): string
    {
        return "{$this->day_name}: {$this->slot_label}";
    }
}
