<?php

// app/Models/OrderItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'name',
        'price',
        'qty',
        'image',
        'brand',
        'warranty_start',
        'warranty_end',
    ];

    protected $casts = [
        'price' => 'float',
        'qty' => 'integer',
        'warranty_start' => 'date',
        'warranty_end' => 'date',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Resolve buy / warranty start date, falling back to order creation date.
     */
    public function getResolvedWarrantyStartAttribute(): ?\Carbon\Carbon
    {
        if ($this->warranty_start) {
            return \Carbon\Carbon::parse($this->warranty_start);
        }
        if ($this->order && $this->order->created_at) {
            return \Carbon\Carbon::parse($this->order->created_at);
        }
        return null;
    }

    /**
     * Resolve warranty end date, falling back to product warranty definition if available.
     */
    public function getResolvedWarrantyEndAttribute(): ?\Carbon\Carbon
    {
        if ($this->warranty_end) {
            return \Carbon\Carbon::parse($this->warranty_end);
        }
        $start = $this->resolved_warranty_start;
        $warrantyStr = $this->product?->warranty;
        if ($start && $warrantyStr) {
            return static::calculateEndDate($start, $warrantyStr);
        }
        return null;
    }

    /**
     * Compute warranty end date from a start date and warranty duration string.
     */
    public static function calculateEndDate(\Carbon\Carbon $start, string $warrantyStr): ?\Carbon\Carbon
    {
        $w = strtolower(trim($warrantyStr));
        if (preg_match('/(\d+)\s*(year|yr|y)/i', $w, $m)) {
            return $start->copy()->addYears((int) $m[1]);
        }
        if (preg_match('/(\d+)\s*(month|mon|m)/i', $w, $m)) {
            return $start->copy()->addMonths((int) $m[1]);
        }
        if (preg_match('/(\d+)\s*(day|d)/i', $w, $m)) {
            return $start->copy()->addDays((int) $m[1]);
        }
        if (is_numeric($w)) {
            return $start->copy()->addYears((int) $w);
        }
        return null;
    }

    /**
     * Formatted string for warranty display: Buy: DD.MM.YYYY → End: DD.MM.YYYY
     */
    public function getWarrantyDisplayAttribute(): ?string
    {
        $start = $this->resolved_warranty_start;
        $end = $this->resolved_warranty_end;
        if ($start && $end) {
            return "Buy: " . $start->format('d.m.Y') . " → End: " . $end->format('d.m.Y');
        } elseif ($start) {
            return "Buy: " . $start->format('d.m.Y');
        }
        return null;
    }
}

