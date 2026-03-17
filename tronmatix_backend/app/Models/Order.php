<?php

// app/Models/Order.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    // ── Valid order statuses ──────────────────────────────────────────────────
    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_SHIPPED = 'shipped';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_CANCELLED = 'cancelled';

    // ── Mass assignable ───────────────────────────────────────────────────────
    protected $fillable = [
        'order_id',
        'user_id',
        'status',
        'payment_method',
        'payment_status',
        'payment_ref',
        'subtotal',
        'discount_id',          // FK → discounts
        'discount_code',
        'discount_amount',
        'delivery',
        'tax',
        'total',
        'location_id',          // FIX [1]: FK → user_locations — was missing
        'shipping',             // JSON snapshot {name, phone, address, city, country, note}
        'delivery_date',
        'delivery_time_slot',
        'delivery_confirmed_at',
    ];

    // ── Casts ─────────────────────────────────────────────────────────────────
    protected $casts = [
        'shipping' => 'array',
        'subtotal' => 'float',
        'discount_amount' => 'float',
        'delivery' => 'float',
        'tax' => 'float',
        'total' => 'float',
        'delivery_date' => 'date',
        'delivery_confirmed_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    // FIX [2]: discount() relationship — DiscountController uses $order->discount
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    // FIX [3]: location() relationship — order-show blade uses $order->location
    public function location(): BelongsTo
    {
        return $this->belongsTo(UserLocation::class, 'location_id');
    }

    // FIX [4]: activePayment() — used by payment controllers to get current payment
    public function activePayment(): HasOne
    {
        return $this->hasOne(Payment::class)->whereNotIn('status', [
            Payment::STATUS_PAID,
            Payment::STATUS_EXPIRED,
            Payment::STATUS_FAILED,
        ])->latestOfMany();
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    // ── Boot ──────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        // Auto-generate readable order_id on create
        static::creating(function (Order $order) {
            if (empty($order->order_id)) {
                $order->order_id = 'TRX-'.strtoupper(substr(uniqid(), -8));
            }
            if (empty($order->status)) {
                $order->status = self::STATUS_CONFIRMED;
            }
            if (empty($order->payment_status)) {
                $order->payment_status = 'pending';
            }
        });

        // FIX [5]: VIP threshold now reads from AdminSetting instead of hardcoded 1000
        static::saved(function (Order $order) {
            if (! $order->user_id) {
                return;
            }

            $user = User::find($order->user_id);
            if (! $user || $user->role !== 'customer') {
                return;
            }

            $vipThreshold = (float) AdminSetting::get('vip_threshold', 1000);

            $totalSpent = static::where('user_id', $order->user_id)
                ->whereNotIn('status', [self::STATUS_CANCELLED])
                ->sum('total');

            if ($totalSpent >= $vipThreshold) {
                $user->update(['role' => 'vip']);
            }
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /** Total quantity of items in this order */
    public function totalQty(): int
    {
        return $this->items->sum('qty');
    }

    /** Human-readable status badge config for blade views */
    public function statusBadge(): array
    {
        return match ($this->status) {
            self::STATUS_PENDING => ['label' => '⏳ PENDING',    'color' => '#eab308'],
            self::STATUS_CONFIRMED => ['label' => '✅ CONFIRMED',  'color' => '#3b82f6'],
            self::STATUS_PROCESSING => ['label' => '⚙️ PROCESSING', 'color' => '#8b5cf6'],
            self::STATUS_SHIPPED => ['label' => '🚚 SHIPPED',    'color' => '#F97316'],
            self::STATUS_DELIVERED => ['label' => '📦 DELIVERED',  'color' => '#22c55e'],
            self::STATUS_CANCELLED => ['label' => '❌ CANCELLED',  'color' => '#ef4444'],
            default => ['label' => strtoupper($this->status), 'color' => '#fff'],
        };
    }
}
