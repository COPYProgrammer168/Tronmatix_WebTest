<?php

// app/Models/Discount.php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discount extends Model
{
    protected $fillable = [
        'code', 'type', 'value',
        'min_order', 'max_uses', 'used_count',
        'expires_at', 'is_active', 'categories',
        'badge_config',  // { text, icon, bg, border, color } — shown on product cards
    ];

    protected $casts = [
        'value' => 'float',
        'min_order' => 'float',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'is_active' => 'boolean',
        'categories' => 'array',
        'expires_at' => 'datetime',
        'badge_config' => 'array',  // stored as JSON, cast to/from PHP array
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    // FIX [2]: scopeActive — DiscountController lists active, non-expired codes
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now())
            );
    }

    public function scopeExpired(Builder $q): Builder
    {
        return $q->where('expires_at', '<', now());
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Compute discount amount for a given subtotal.
     */
    public function calcAmount(float $subtotal): float
    {
        if ($this->type === 'percentage') {
            return round($subtotal * ($this->value / 100), 2);
        }

        return min($this->value, $subtotal); // fixed — never exceed subtotal
    }

    // FIX [1]: isUsable() now accepts $subtotal so min_order is validated in one place
    public function isUsable(float $subtotal = 0): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        if ($this->max_uses && $this->used_count >= $this->max_uses) {
            return false;
        }
        if ($subtotal < $this->min_order) {
            return false;
        }  // was missing

        return true;
    }

    /**
     * For badge display only — checks active/expired/exhausted but NOT min_order.
     * A product card badge is decorative; the real min_order check happens at apply-time.
     */
    public function isActiveForBadge(): bool
    {
        if (! $this->is_active) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->max_uses && $this->used_count >= $this->max_uses) return false;
        return true;
    }

    /** Increment usage count after successful order */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }

    /** Status label for blade views */
    public function getStatusAttribute(): string
    {
        if (! $this->is_active) {
            return 'disabled';
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'expired';
        }
        if ($this->max_uses && $this->used_count >= $this->max_uses) {
            return 'exhausted';
        }

        return 'active';
    }
}
