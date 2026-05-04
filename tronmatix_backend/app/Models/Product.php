<?php

// app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AdminSetting;

class Product extends Model
{
    // ── Mass assignable ───────────────────────────────────────────────────────
    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
        'brand',
        'image',
        'image_disk',
        'images',
        'specs',
        'stock',
        'rating',
        'is_featured',
        'is_hot',
    ];

    // ── Casts ─────────────────────────────────────────────────────────────────
    protected $casts = [
        'price' => 'decimal:2',
        'rating' => 'decimal:1',
        'stock' => 'integer',
        'is_featured' => 'boolean',
        'is_hot' => 'boolean',
        'images' => 'array',
        'specs' => 'array',
    ];

    // ── Appended virtual attributes ───────────────────────────────────────────
    protected $appends = ['all_images', 'in_stock', 'display_price'];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Unified images array — bridges single `image` (old) and `images[]` (new).
     * Frontend can always use product.all_images[] without null-checks.
     */
    public function getAllImagesAttribute(): array
    {
        $images = is_array($this->images) ? array_values(array_filter($this->images)) : [];

        if (empty($images) && ! empty($this->image)) {
            $images = [$this->image];
        }

        return $images;
    }

    /** True when stock is null (unlimited) or > 0 */
    public function getInStockAttribute(): bool
    {
        return $this->stock === null || $this->stock > 0;
    }

    /** Price as float — avoids string "123.45" from decimal cast confusing JS */
    public function getDisplayPriceAttribute(): float
    {
        return (float) $this->price;
    }

    // ── Mutators ──────────────────────────────────────────────────────────────

    /**
     * Sync `image` with images[0] whenever images[] is saved.
     */
    public function setImagesAttribute(?array $value): void
    {
        $clean = array_values(array_filter((array) $value));
        $this->attributes['images'] = json_encode($clean);

        if (! empty($clean)) {
            $this->attributes['image'] = $clean[0];
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Decrement stock atomically. Returns false if insufficient.
     */
    public function decrementStock(int $qty = 1): bool
    {
        if ($this->stock !== null && $this->stock < $qty) {
            return false;
        }
        if ($this->stock !== null) {
            $this->decrement('stock', $qty);
        }

        return true;
    }

    /** Add an image path to the images array (max 8) */
    public function addImage(string $path): void
    {
        $images = $this->all_images;
        $images[] = $path;
        $this->images = array_slice(array_unique($images), 0, 8);
        $this->save();
    }

    /** Remove an image by exact path */
    public function removeImage(string $path): void
    {
        $this->images = array_values(array_filter(
            $this->all_images, fn ($img) => $img !== $path
        ));
        $this->save();
    }

    // FIX [2]: reads AdminSetting instead of hardcoded 5
    public function isLowStock(): bool
    {
        $threshold = (int) AdminSetting::get('notif_low_stock_threshold', 5);

        return $this->stock !== null && $this->stock > 0 && $this->stock <= $threshold;
    }

    /** True when completely out of stock */
    public function isOutOfStock(): bool
    {
        return $this->stock !== null && $this->stock <= 0;
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeFeatured($q)
    {
        return $q->where('is_featured', true);
    }

    public function scopeHot($q)
    {
        return $q->where('is_hot', true);
    }

    public function scopeInStock($q)
    {
        return $q->where(fn ($q) => $q->whereNull('stock')->orWhere('stock', '>', 0));
    }

    public function scopeByCategory($q, string $cat)
    {
        return $q->whereRaw('LOWER(category) = ?', [strtolower($cat)]);
    }

    public function scopeLowStock($q)
    {
        $threshold = (int) AdminSetting::get('notif_low_stock_threshold', 5);

        return $q->where('stock', '>', 0)->where('stock', '<=', $threshold);
    }
}
