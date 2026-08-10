<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Feature extends Model
{
    protected $fillable = ['key', 'label', 'icon', 'category', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('ordered', fn (Builder $q) => $q->orderBy('sort_order'));
    }

    /** Return all feature slugs as a flat array */
    public static function allKeys(): array
    {
        return static::pluck('key')->toArray();
    }

    /** Return feature metadata keyed by slug */
    public static function metaMap(): array
    {
        return static::all()->mapWithKeys(fn ($f) => [
            $f->key => [
                'label' => $f->label,
                'icon'  => $f->icon,
                'category' => $f->category,
            ],
        ])->toArray();
    }
}
