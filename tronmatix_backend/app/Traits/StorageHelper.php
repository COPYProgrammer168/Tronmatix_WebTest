<?php

// app/Traits/StorageHelper.php
//
// Used by DashboardController (and any other controller that needs to
// store / delete files without injecting ImageStorageService directly).
//
// Methods mirrored from ImageStorageService so both code paths stay in sync.

namespace App\Traits;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait StorageHelper
{
    // ── Public-facing helpers (called by DashboardController methods) ──────────

    /**
     * Upload a file and return the DB-ready path.
     *   Local  → "/storage/products/uuid.jpg"
     *   Cloud  → "https://pub-xxx.r2.dev/products/uuid.jpg"
     */
    protected function storeFile(UploadedFile $file, string $folder = 'products'): ?string
    {
        try {
            $ext      = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
            $filename = Str::uuid() . '.' . $ext;
            $path     = $folder . '/' . $filename;

            if ($this->storageUsingCloud()) {
                Storage::disk('s3')->putFileAs(
                    dirname($path),
                    $file,
                    basename($path),
                    ['visibility' => 'public']
                );

                /** @var FilesystemAdapter $disk */
                $disk = Storage::disk('s3');
                return $disk->url($path);
            }

            Storage::disk('public')->putFileAs(dirname($path), $file, basename($path));
            return '/storage/' . $path;

        } catch (\Throwable $e) {
            Log::warning('[StorageHelper] storeFile failed', [
                'folder' => $folder,
                'error'  => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Delete a file by its DB path.
     * Silently skips paths that don't belong to this app's storage.
     */
    protected function deleteStorageFile(?string $dbPath): void
    {
        if (!$dbPath || trim($dbPath) === '') return;

        try {
            if ($this->storageUsingCloud()) {
                if (!$this->storageIsOurCloudUrl($dbPath)) return;
                $key = $this->storageCloudPathToKey($dbPath);
                if ($key) Storage::disk('s3')->delete($key);
            } else {
                // Never delete an external URL from local disk
                if (Str::startsWith($dbPath, ['http://', 'https://'])) return;
                $relative = $this->storageLocalPathToRelative($dbPath);
                if ($relative) Storage::disk('public')->delete($relative);
            }
        } catch (\Throwable $e) {
            Log::warning('[StorageHelper] deleteStorageFile failed', [
                'path'  => $dbPath,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ── Private helpers ────────────────────────────────────────────────────────
    // Prefixed with "storage" to avoid clashing with any method the consuming
    // controller may define with short names like usingCloud().

    private function storageUsingCloud(): bool
    {
        return !in_array(config('filesystems.default', 'public'), ['public', 'local']);
    }

    private function storageIsOurCloudUrl(string $url): bool
    {
        $base = config('filesystems.disks.s3.url')
            ?: config('filesystems.disks.s3.endpoint', '');

        return $base && Str::startsWith($url, rtrim($base, '/'));
    }

    private function storageCloudPathToKey(string $url): ?string
    {
        $base = rtrim(
            config('filesystems.disks.s3.url')
                ?: config('filesystems.disks.s3.endpoint', ''),
            '/'
        );

        if ($base && Str::startsWith($url, $base)) {
            return ltrim(substr($url, strlen($base)), '/');
        }

        return ltrim(parse_url($url, PHP_URL_PATH) ?? '', '/');
    }

    private function storageLocalPathToRelative(string $path): ?string
    {
        if (Str::startsWith($path, '/storage/')) {
            return substr($path, strlen('/storage/'));
        }
        // Bare relative path (legacy): "products/abc.jpg"
        if (!Str::startsWith($path, ['/', 'http'])) {
            return $path;
        }
        return null;
    }
}
