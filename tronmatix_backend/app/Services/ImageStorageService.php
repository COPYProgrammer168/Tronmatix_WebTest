<?php

// app/Services/ImageStorageService.php

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageStorageService
{
    /**
     * Store an uploaded image.
     *
     * Returns the DB-ready path:
     *   Local  → "/storage/products/uuid.jpg"   (relative, no host)
     *   Cloud  → "https://pub-xxx.r2.dev/..."   (full HTTPS URL)
     */
    public function store(UploadedFile $file, string $folder = 'products'): string
    {
        $ext      = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
        $filename = Str::uuid() . '.' . $ext;
        $path     = $folder . '/' . $filename;

        return $this->usingCloud()
            ? $this->storeCloud($file, $path)
            : $this->storeLocal($file, $path);
    }

    /**
     * Delete an image by its DB path.
     * Silently skips external URLs we didn't upload.
     */
    public function delete(?string $dbPath): void
    {
        if (!$dbPath || trim($dbPath) === '') return;

        try {
            if ($this->usingCloud()) {
                if (!$this->isOurCloudUrl($dbPath)) return;

                $key = $this->cloudPathToKey($dbPath);
                if ($key) Storage::disk('s3')->delete($key);
            } else {
                if (Str::startsWith($dbPath, ['http://', 'https://'])) return;

                $relative = $this->localPathToRelative($dbPath);
                if ($relative) Storage::disk('public')->delete($relative);
            }
        } catch (\Throwable $e) {
            Log::warning('[ImageStorageService] delete failed', [
                'path'  => $dbPath,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete multiple images by DB paths.
     */
    public function deleteMany(array $paths): void
    {
        foreach ($paths as $path) {
            $this->delete($path);
        }
    }

    /**
     * Resolve a DB path to a publicly accessible URL.
     */
    public function resolveUrl(?string $dbPath): ?string
    {
        if (!$dbPath) return null;

        if (Str::startsWith($dbPath, ['http://', 'https://'])) {
            return $dbPath;
        }

        return url(ltrim($dbPath, '/'));
    }

    // ── Private ───────────────────────────────────────────────────────────────

    /**
     * Returns true only when BOTH conditions are met:
     *   1. FILESYSTEM_DISK is set to something other than 'public' / 'local'
     *   2. The league/flysystem-aws-s3-v3 package is actually installed
     *
     * This prevents the fatal "PortableVisibilityConverter not found" crash
     * that happens when FILESYSTEM_DISK=s3 in .env but the composer package
     * hasn't been installed yet (common in local / CI environments).
     *
     * To install the package:
     *   composer require league/flysystem-aws-s3-v3 "^3.0"
     */
    private function usingCloud(): bool
    {
        $disk = config('filesystems.default', 'public');

        // Fast exit — explicitly local
        if (in_array($disk, ['public', 'local'])) {
            return false;
        }

        // Guard: check the S3 adapter class exists before attempting to use it.
        // If the package isn't installed, fall back to local storage and log a
        // one-time warning so the developer knows what to install.
        if (!class_exists(\League\Flysystem\AwsS3V3\PortableVisibilityConverter::class)) {
            Log::warning(
                '[ImageStorageService] FILESYSTEM_DISK is set to "' . $disk . '" but ' .
                'league/flysystem-aws-s3-v3 is not installed. ' .
                'Falling back to local storage. ' .
                'Run: composer require league/flysystem-aws-s3-v3 "^3.0"'
            );
            return false;
        }

        return true;
    }

    private function storeLocal(UploadedFile $file, string $path): string
    {
        Storage::disk('public')->putFileAs(
            dirname($path),
            $file,
            basename($path)
        );

        return '/storage/' . $path;
    }

    private function storeCloud(UploadedFile $file, string $path): string
    {
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

    private function isOurCloudUrl(string $url): bool
    {
        $base = config('filesystems.disks.s3.url')
            ?: config('filesystems.disks.s3.endpoint', '');

        return $base && Str::startsWith($url, rtrim($base, '/'));
    }

    private function cloudPathToKey(string $url): ?string
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

    private function localPathToRelative(string $path): ?string
    {
        if (Str::startsWith($path, '/storage/')) {
            return substr($path, strlen('/storage/'));
        }

        if (!Str::startsWith($path, ['/', 'http'])) {
            return $path;
        }

        return null;
    }
}