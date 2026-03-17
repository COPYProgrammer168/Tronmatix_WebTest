<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    // ── Valid statuses ────────────────────────────────────────────────────────
    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_FAILED = 'failed';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_MANUAL_PENDING = 'manual_pending';

    public const STATUS_REFUNDED = 'refunded';

    // ── Mass assignable ───────────────────────────────────────────────────────
    protected $fillable = [
        // Identity
        'order_id', 'tran_id', 'provider', 'payment_method',
        // Amount
        'amount', 'currency',
        // QR / KHQR
        'qr_data', 'qr_md5',
        'qr_expiration',
        'qr_expires_at',
        // Bakong API response
        'apv', 'bakong_hash', 'from_account_id', 'to_account_id',
        'description', 'transaction_id',
        // Status
        'status', 'paid', 'paid_at', 'expires_at',
        // Extra
        'meta',
    ];

    // ── Type casts ────────────────────────────────────────────────────────────
    protected $casts = [
        'amount' => 'decimal:2',
        'qr_expiration' => 'integer',    // legacy BIGINT ms
        'qr_expires_at' => 'datetime',
        'paid' => 'boolean',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // ── Query scopes ──────────────────────────────────────────────────────────

    public function scopePending(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_PENDING);
    }

    public function scopePaid(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_PAID);
    }

    public function scopeExpired(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_EXPIRED);
    }

    public function scopeFailed(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_FAILED);
    }

    /** Active = not in a terminal state */
    public function scopeActive(Builder $q): Builder
    {
        return $q->whereNotIn('status', [
            self::STATUS_PAID, self::STATUS_FAILED,
            self::STATUS_EXPIRED, self::STATUS_REFUNDED,
        ]);
    }

    // scopeForOrder — used by CheckPaymentController
    public function scopeForOrder(Builder $q, int|string $orderId): Builder
    {
        return $q->where('order_id', $orderId);
    }

    // scopeExpiredQr — used by DashboardController::notifications()
    public function scopeExpiredQr(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_PENDING)
            ->where('qr_expires_at', '<', now());
    }

    // ── State checks ──────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isManualPending(): bool
    {
        return $this->status === self::STATUS_MANUAL_PENDING;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID || $this->paid === true;
    }

    public function isExpired(): bool
    {
        if ($this->status === self::STATUS_EXPIRED) {
            return true;
        }
        // Preferred: proper datetime column
        if ($this->qr_expires_at && $this->qr_expires_at->isPast()) {
            return true;
        }
        // Fallback: overall session expiry
        if ($this->expires_at && $this->expires_at->isPast()) {
            return true;
        }
        // Legacy: BIGINT ms from Node.js
        if ($this->qr_expiration && (int) (microtime(true) * 1000) > $this->qr_expiration) {
            return true;
        }

        return false;
    }

    public function isActive(): bool
    {
        return ! $this->isPaid() && ! $this->isExpired();
    }

    // ── Mutations ─────────────────────────────────────────────────────────────
    // syncs parent order->payment_status so dashboard stays consistent
    public function markAsPaid(?string $bakongHash = null, array $bakongData = []): bool
    {
        $updated = $this->update([
            'status' => self::STATUS_PAID,
            'paid' => true,
            'paid_at' => now(),
            'bakong_hash' => $bakongHash ?? $this->bakong_hash,
            'transaction_id' => $bakongHash ?? $this->transaction_id,
            'from_account_id' => $bakongData['fromAccountId'] ?? $this->from_account_id,
            'to_account_id' => $bakongData['toAccountId'] ?? $this->to_account_id,
            'description' => $bakongData['description'] ?? $this->description,
        ]);

        // Keep parent order in sync
        $this->order?->update([
            'payment_status' => 'paid',
            'payment_ref' => $bakongHash ?? $this->bakong_hash,
        ]);

        return $updated;
    }

    public function markAsExpired(): bool
    {
        return $this->update(['status' => self::STATUS_EXPIRED,        'paid' => false]);
    }

    public function markAsFailed(): bool
    {
        return $this->update(['status' => self::STATUS_FAILED,         'paid' => false]);
    }

    public function markAsManualPending(): bool
    {
        return $this->update(['status' => self::STATUS_MANUAL_PENDING]);
    }

    public function markAsRefunded(): bool
    {
        return $this->update(['status' => self::STATUS_REFUNDED]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Read raw legacy BIGINT ms — bypasses integer cast to avoid re-casting issues */
    public function getQrExpirationMsAttribute(): int
    {
        return (int) ($this->attributes['qr_expiration'] ?? ($this->meta['qr_expiration'] ?? 0));
    }

    /** Check expiry using legacy ms value (Node.js compat) */
    public function isQrCurrentlyExpired(): bool
    {
        $ms = $this->qr_expiration_ms;

        return $ms > 0 && (int) (microtime(true) * 1000) > $ms;
    }

    // return type resolves now — Carbon imported at top
    public function getEffectiveExpiration(): ?Carbon
    {
        return $this->qr_expires_at ?? $this->expires_at ?? null;
    }

    /** Badge data for blade views */
    public function statusBadge(): array
    {
        return match ($this->status) {
            self::STATUS_PAID => ['label' => '✅ PAID',      'color' => '#22c55e', 'bg' => 'rgba(34,197,94,0.12)',    'border' => 'rgba(34,197,94,0.3)'],
            self::STATUS_PENDING => ['label' => '⏳ PENDING',   'color' => '#eab308', 'bg' => 'rgba(234,179,8,0.12)',    'border' => 'rgba(234,179,8,0.3)'],
            self::STATUS_EXPIRED => ['label' => '⌛ EXPIRED',   'color' => '#6b7280', 'bg' => 'rgba(107,114,128,0.12)', 'border' => 'rgba(107,114,128,0.3)'],
            self::STATUS_FAILED => ['label' => '❌ FAILED',    'color' => '#ef4444', 'bg' => 'rgba(239,68,68,0.12)',    'border' => 'rgba(239,68,68,0.3)'],
            self::STATUS_MANUAL_PENDING => ['label' => '⚠️ VERIFY',   'color' => '#F97316', 'bg' => 'rgba(249,115,22,0.12)',   'border' => 'rgba(249,115,22,0.3)'],
            self::STATUS_REFUNDED => ['label' => '↩️ REFUNDED', 'color' => '#a78bfa', 'bg' => 'rgba(167,139,250,0.12)', 'border' => 'rgba(167,139,250,0.3)'],
            default => ['label' => strtoupper($this->status), 'color' => '#fff', 'bg' => 'rgba(255,255,255,0.07)', 'border' => 'rgba(255,255,255,0.1)'],
        };
    }
}
