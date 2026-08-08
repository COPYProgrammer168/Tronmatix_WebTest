<?php

// app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AdminSetting;
use Illuminate\Support\Str;

class Product extends Model
{
    // ── Mass assignable ───────────────────────────────────────────────────────
    protected $fillable = [
        'name',
        'slug',
        'sku',
        'caption',
        'description',
        'price',
        'category',
        'brand',
        'brand_id',
        'warranty',
        'image',
        'image_disk',
        'images',
        'specs',
        'specs_title',
        'current_stock',
        'cost_price',
        'low_stock_threshold',
        'stock_status',
        'stock_details',
        'brand_pc_part',
        'rating',
        'is_featured',
        'is_hot',
    ];

    // ── Casts ─────────────────────────────────────────────────────────────────
    protected $casts = [
        'price' => 'string',
        'rating' => 'decimal:1',
        'current_stock' => 'integer',
        'cost_price' => 'decimal:2',
        'low_stock_threshold' => 'integer',
        'is_featured' => 'boolean',
        'is_hot' => 'boolean',
        'images' => 'array',
        'specs' => 'array',
    ];

    // ── Appended virtual attributes ───────────────────────────────────────────
    protected $appends = ['all_images', 'in_stock', 'display_price'];

    // ── Boot ──────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Product $product) {
            // Auto-fill a random 1–3 year warranty whenever none is set, so
            // every product always carries one ("1 year" | "2 years" | "3 years").
            // OrderController parses this string to derive each order item's
            // warranty_start/end dates.
            if (empty($product->warranty)) {
                $years = [1, 2, 3][array_rand([1, 2, 3])];
                $product->warranty = $years === 1 ? '1 year' : "{$years} years";
            }

            // Auto-update stock_status based on stock count
            if ($product->stock !== null && $product->stock <= 0) {
                $product->stock_status = 'Sold Out';
            } elseif ($product->isDirty('current_stock') && $product->stock > 0 && ($product->stock_status === 'Sold Out' || empty($product->stock_status))) {
                $product->stock_status = 'Available InStock Now';
            }

            // Auto-generate SKU from category ({PREFIX}{5 random chars}, e.g. CPUA7BQP).
            // Only fills when empty — SKUs are permanent once created and never
            // re-derived on update (sku is not part of the edit payload).
            if (empty($product->sku)) {
                $product->sku = \App\Services\SkuGenerator::generate($product->category);
            }

            // Auto-generate slug from name
            if ($product->isDirty('name') || ! $product->slug) {
                $base = Str::slug($product->name);
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                    $slug = $base . '-' . $i++;
                }
                $product->slug = $slug;
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Backwards-compatible `stock` accessor → reads the current_stock column.
     * The DB column was renamed to `current_stock` (system-of-record for the
     * inventory ledger), but the entire storefront + API + order flow still
     * reads `$product->stock`, so map it through here to avoid touching every
     * consumer. Null still means "unlimited".
     */
    public function getStockAttribute(): ?int
    {
        return $this->current_stock;
    }

    /**
     * Backwards-compatible `stock` mutator → writes current_stock.
     * Lets existing code do `$product->stock = X` / mass-assign `'stock'`.
     */
    public function setStockAttribute(?int $value): void
    {
        $this->attributes['current_stock'] = $value;
    }

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
            $this->decrement('current_stock', $qty);
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

    // Uses the per-product low_stock_threshold column (falls back to the global
    // AdminSetting when the column is unset/0).
    public function isLowStock(): bool
    {
        $threshold = $this->low_stock_threshold
            ?: (int) AdminSetting::get('notif_low_stock_threshold', 5);

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
        return $q->where(fn ($q) => $q->whereNull('current_stock')->orWhere('current_stock', '>', 0));
    }

    public function scopeByCategory($q, string $cat)
    {
        return $q->whereRaw('LOWER(category) = ?', [strtolower($cat)]);
    }

    public function scopeLowStock($q)
    {
        $threshold = (int) AdminSetting::get('notif_low_stock_threshold', 5);

        return $q->where('current_stock', '>', 0)->where('current_stock', '<=', $threshold);
    }
}
