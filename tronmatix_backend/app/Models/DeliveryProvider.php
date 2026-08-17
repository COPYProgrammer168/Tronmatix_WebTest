<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryProvider extends Model
{
    protected $fillable = ['delivery_zone_id', 'name', 'logo', 'fee', 'estimated_time', 'is_active', 'sort_order'];

    protected $casts = [
        'fee' => 'float',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class);
    }

    public function zones(): HasMany
    {
        return $this->hasMany(DeliveryProviderZone::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * The per-zone fee/time row for a given zone, or null if this provider
     * doesn't serve that zone.
     *
     * @param  string  $zone  'phnom_penh' | 'province'
     */
    public function zoneDetails(string $zone): ?DeliveryProviderZone
    {
        return $this->zones()->where('zone', $zone)->first();
    }
}
