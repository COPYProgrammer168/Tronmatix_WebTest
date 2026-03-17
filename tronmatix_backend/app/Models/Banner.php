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
     * Return a fully-resolved video URL for the frontend.
     * For uploaded files: prepend LARAVEL_URL/storage/…
     * For YouTube/Vimeo embeds: return as-is.
     */
    public function getVideoUrlAttribute(): ?string
    {
        if (! $this->video) {
            return null;
        }

        // Already a full URL (YouTube / Vimeo embed)
        if (str_starts_with($this->video, 'http://') || str_starts_with($this->video, 'https://')) {
            return $this->video;
        }

        // Stored path like /storage/banners/videos/xxx.mp4
        return $this->video;
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
        return $this->image
            && strtolower(pathinfo($this->image, PATHINFO_EXTENSION)) === 'gif';
    }
}
