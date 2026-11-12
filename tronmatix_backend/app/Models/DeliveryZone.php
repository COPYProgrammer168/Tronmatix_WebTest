<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryZone extends Model
{
    protected $fillable = ['name', 'slug'];

    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class);
    }

    public function deliveryProviders(): HasMany
    {
        return $this->hasMany(DeliveryProvider::class);
    }
}
