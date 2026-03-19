<?php

// app/Models/Banner.php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'badge',
        'bg_color', 'text_color',
        'image', 'order', 'active',
        // ── Video support ─────────────────────────────────────────────────────
        'video',        // path or URL to video file (mp4/webm)
        'video_type',   // 'upload' | 'youtube' | 'vimeo' | 'facebook'
    ];

    protected $casts = [
        'active' => 'boolean',
        'order' => 'integer',
    ];

    // ── Default ordering ──────────────────────────────────────────────────────
    protected static function booted(): void
    {
        static::addGlobalScope('ordered', fn (Builder $q) => $q->orderBy('order'));
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('active', true);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Return the video URL for the frontend.
     * BannerController now stores full S3/R2 URLs for uploaded files and
     * embed URLs for YouTube/Vimeo/Facebook — all returned as-is.
     */
    public function getVideoUrlAttribute(): ?string
    {
        return $this->video ?: null;
    }

    /**
     * True if this banner has a video (regardless of type).
     */
    public function getHasVideoAttribute(): bool
    {
        return ! empty($this->video);
    }

    public function getIsGifAttribute(): bool
    {
        if (! $this->image) return false;
        // parse_url extracts the path before any query string — safe for S3/R2 URLs
        // e.g. https://r2.../banners/uuid.gif?X-Amz-... → 'gif'
        $path = parse_url($this->image, PHP_URL_PATH) ?? $this->image;
        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'gif';
    }
}
