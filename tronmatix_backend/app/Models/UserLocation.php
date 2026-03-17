<?php

// app/Models/UserLocation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class UserLocation extends Model
{
    protected $fillable = [
        'user_id', 'name', 'phone',
        'address', 'city', 'country', 'note',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // FIX [1]: orders() relationship — allows querying orders linked to this address
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'location_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    // FIX [2]: wrapped in DB::transaction to prevent race condition where two
    //          concurrent requests could set two locations as default
    public function setAsDefault(): void
    {
        DB::transaction(function () {
            UserLocation::where('user_id', $this->user_id)
                ->where('id', '!=', $this->id)
                ->update(['is_default' => false]);

            $this->update(['is_default' => true]);
        });
    }

    /** Returns shipping array for order snapshot */
    public function toShippingArray(): array
    {
        return [
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'note' => $this->note,
        ];
    }
}
