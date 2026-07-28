<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }
}
