<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    protected $fillable = ['sub_category_id', 'name', 'slug', 'image', 'order', 'is_active'];

    protected $casts = [
        'order'     => 'integer',
        'is_active' => 'boolean',
    ];

    // ── Boot ─────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        // Auto-regenerate the slug whenever the name changes, so an edit in the
        // dashboard (tree page or standalone form) always keeps the URL slug in
        // sync. Uses a unique slug so renames to an existing name get "-2", …
        static::saving(function (Brand $model) {
            if ($model->isDirty('name') || ! $model->slug) {
                $model->slug = unique_slug($model->name, static::class, $model->id);
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('order');
    }
}
