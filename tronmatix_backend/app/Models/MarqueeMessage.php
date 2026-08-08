<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MarqueeMessage extends Model
{
    protected $fillable = ['route', 'text_en', 'text_kh', 'is_active', 'order'];

    protected $casts = [
        'is_active' => 'boolean',
        'order'     => 'integer',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('ordered', fn (Builder $q) => $q->orderBy('order'));
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }
}
