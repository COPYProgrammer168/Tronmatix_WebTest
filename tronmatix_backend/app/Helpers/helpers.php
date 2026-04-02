<?php

// app/helpers.php
//
// Global helpers auto-loaded by composer.json:
//   "autoload": { "files": ["app/helpers.php"] }
//
// After adding, run: composer dump-autoload

if (! function_exists('storage_url')) {
    /**
     * Resolve a DB image/video path to a public URL usable in <img src> and <video src>.
     *
     * Handles:
     *   Local  : "/storage/products/uuid.webp" → "http://yoursite.com/storage/products/uuid.webp"
     *   Cloud  : "https://bucket.r2.dev/..."   → returned as-is
     *   null   : returns $fallback (default null)
     *
     * Usage in Blade:
     *   <img src="{{ storage_url($b->image) }}">
     *   <video src="{{ storage_url($b->video) }}">
     *
     * @param string|null $path     DB value
     * @param string|null $fallback Returned when $path is empty
     */
    function storage_url(?string $path, ?string $fallback = null): ?string
    {
        if (!$path || trim($path) === '') return $fallback;

        $path = trim($path);

        // Already a full URL (cloud, CDN, or external paste) — use as-is
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Local storage path — prefix with app URL
        return url(ltrim($path, '/'));
    }
}
