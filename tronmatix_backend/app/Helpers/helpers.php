<?php

// app/helpers.php
//
// Global helpers auto-loaded by composer.json:
//   "autoload": { "files": ["app/helpers.php"] }
//
// After adding, run: composer dump-autoload

use Illuminate\Support\Facades\Auth;

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

// ── Dashboard auth helpers ────────────────────────────────────────────────────
// These helpers resolve the current dashboard user regardless of which guard
// they authenticated under (admin guard for superadmin/admin, staff guard for
// editor/seller/delivery/developer).

if (! function_exists('dashboard_user')) {
    /**
     * Returns the currently authenticated dashboard user.
     * Checks admin guard first, then staff guard.
     *
     * @return \App\Models\Admin|\App\Models\Staff|null
     */
    function dashboard_user(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return Auth::guard('admin')->user() ?? Auth::guard('staff')->user();
    }
}

if (! function_exists('dashboard_role')) {
    /**
     * Returns the role string of the currently authenticated dashboard user.
     * Falls back to 'editor' (most restrictive staff role) if nobody is authenticated.
     */
    function dashboard_role(): string
    {
        return dashboard_user()?->role ?? 'editor';
    }
}

if (! function_exists('is_admin_guard')) {
    /**
     * True if the current user authenticated via the admin guard (superadmin/admin).
     * Use this in Blade to gate admin-only UI elements.
     */
    function is_admin_guard(): bool
    {
        return Auth::guard('admin')->check();
    }
}

if (! function_exists('is_staff_guard')) {
    /**
     * True if the current user authenticated via the staff guard
     * (editor/seller/delivery/developer).
     */
    function is_staff_guard(): bool
    {
        return Auth::guard('staff')->check();
    }
}
