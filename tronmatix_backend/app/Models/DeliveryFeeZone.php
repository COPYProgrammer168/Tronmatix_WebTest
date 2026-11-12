<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

// Distance-based delivery fee tier. Separate from the legacy DeliveryZone
// (province → delivery_provider mapping). Rows are matched against a
// reverse-geocoded province name via province_match; province_match = NULL
// is the default/fallback tier.
class DeliveryFeeZone extends Model
{
    protected $table = 'delivery_fee_zones';

    protected $fillable = [
        'zone_name',
        'province_match',
        'base_fee',
        'free_km',
        'per_km_rate',
        'max_distance_km',
        'road_factor',
        'is_active',
    ];

    protected $casts = [
        'base_fee'         => 'float',
        'free_km'          => 'float',
        'per_km_rate'      => 'float',
        'max_distance_km'  => 'float',
        'road_factor'      => 'float',
        'is_active'        => 'boolean',
    ];

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    /**
     * The default/fallback zone (province_match IS NULL).
     */
    public function scopeDefault(Builder $q): Builder
    {
        return $q->whereNull('province_match');
    }
}
