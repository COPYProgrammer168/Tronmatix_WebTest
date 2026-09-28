<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Role extends Model
{
    protected $fillable = ['key', 'label', 'color', 'icon', 'sort_order', 'is_locked', 'is_staff_portal', 'description', 'locked_features', 'forbidden_features'];

    protected $casts = [
        'sort_order'     => 'integer',
        'is_locked'      => 'boolean',
        'is_staff_portal' => 'boolean',
        'locked_features'    => 'array',
        'forbidden_features' => 'array',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('ordered', fn (Builder $q) => $q->orderBy('sort_order'));
    }

    /** Return all roles ordered by sort_order */
    public static function ordered(): Builder
    {
        return static::query()->orderBy('sort_order');
    }

    /** Return all editable roles (superadmin excluded from add/edit/delete UI) */
    public static function editable(): Builder
    {
        return static::where('key', '!=', 'superadmin');
    }

    /** Return all role slugs as a flat array */
    public static function allKeys(): array
    {
        return static::pluck('key')->toArray();
    }

    /** Return role metadata keyed by slug — mirrors old AdminSetting::getRoleMeta() shape */
    public static function metaMap(): array
    {
        return static::all()->mapWithKeys(fn ($r) => [
            $r->key => [
                'color' => $r->color,
                'icon'  => $r->icon,
                'label' => $r->label,
            ],
        ])->toArray();
    }
}
