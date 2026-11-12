<?php

// app/Models/StockMovement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    // ── Movement types ─────────────────────────────────────────────────────────
    public const TYPE_IN          = 'in';
    public const TYPE_OUT         = 'out';
    public const TYPE_ADJUSTMENT  = 'adjustment';
    public const TYPE_DAMAGED     = 'damaged';
    public const TYPE_REVERSAL    = 'reversal';

    // ── Mass assignable ───────────────────────────────────────────────────────
    protected $fillable = [
        'product_id',
        'type',
        'quantity',          // signed: + increases stock, - decreases
        'unit_cost',         // always positive cost-per-unit basis, never signed
        'note',
        'reference_type',
        'reference_id',
        'reversed_movement_id',
        'created_by',
    ];

    // ── Casts ─────────────────────────────────────────────────────────────────
    protected $casts = [
        'quantity'       => 'integer',
        'unit_cost'      => 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function reversedMovement(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversed_movement_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** True when this movement adds stock (+quantity). */
    public function isInbound(): bool
    {
        return $this->quantity > 0;
    }

    /** True when this movement removes stock (-quantity). */
    public function isOutbound(): bool
    {
        return $this->quantity < 0;
    }

    /** Human-readable type label for views. */
    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_IN         => 'IN',
            self::TYPE_OUT        => 'OUT',
            self::TYPE_ADJUSTMENT => 'ADJUSTMENT',
            self::TYPE_DAMAGED    => 'DAMAGED',
            self::TYPE_REVERSAL   => 'REVERSAL',
            default               => strtoupper($this->type),
        };
    }
}
