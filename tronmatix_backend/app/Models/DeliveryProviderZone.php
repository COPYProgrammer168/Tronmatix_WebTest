<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Per-zone (phnom_penh | province) fee/ETA row for a delivery provider.
// fee = NULL means "negotiable / varies" for that zone.
class DeliveryProviderZone extends Model
{
    protected $fillable = [
        'delivery_provider_id',
        'zone',
        'fee',
        'estimated_time',
    ];

    protected $casts = [
        'fee' => 'float',
    ];

    public function deliveryProvider(): BelongsTo
    {
        return $this->belongsTo(DeliveryProvider::class);
    }
}