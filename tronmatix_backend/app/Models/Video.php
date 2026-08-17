<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'video_type',
        'video',
        'thumbnail',
        'product_id',
        'order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'order' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true)->orderBy('order');
    }

    /**
     * Resolve the playable URL.
     * - 'upload' type: prefix with storage path (like Banner::video_url).
     * - youtube/facebook/tiktok: stored value is already a full URL, return as-is.
     */
    public function getVideoUrlAttribute(): ?string
    {
        if (!$this->video) {
            return null;
        }

        if ($this->video_type !== 'upload') {
            return $this->video;
        }

        if (str_starts_with($this->video, 'http://') || str_starts_with($this->video, 'https://')) {
            return $this->video;
        }

        $clean = preg_replace('/^\/?storage\//', '', $this->video);
        return '/storage/' . ltrim($clean, '/');
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail) {
            return null;
        }

        if (str_starts_with($this->thumbnail, 'http://') || str_starts_with($this->thumbnail, 'https://')) {
            return $this->thumbnail;
        }

        $clean = preg_replace('/^\/?storage\//', '', $this->thumbnail);
        return '/storage/' . ltrim($clean, '/');
    }
}