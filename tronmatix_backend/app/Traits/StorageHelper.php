<?php

// app/Traits/StorageHelper.php
// Shared trait for all controllers that upload files to S3/R2 or local storage.

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait StorageHelper
{
    /**
     * Returns 's3' if FILESYSTEM_DISK=s3, otherwise 'public' (local dev).
     */
    protected function storageDisk(): string
    {
        return config('filesystems.default') === 's3' ? 's3' : 'public';
    }

    /**
     * Store a file and return its public URL.
     *
     * FIX: Storage::disk('s3')->url() does NOT exist in Flysystem v3.
     * Instead use Storage::disk('s3')->temporaryUrl() or build URL manually
     * from AWS_URL env var + the stored path.
     *
     * For public R2/S3 buckets, the URL is: AWS_URL + '/' + path
     */
    protected function storeFile($file, string $folder): string
    {
        $disk = $this->storageDisk();
        $path = $file->store($folder, $disk);

        return $this->storageUrl($disk, $path);
    }

    /**
     * Store a file with a specific filename and return its public URL.
     */
    protected function storeFileAs($file, string $folder, string $filename): string
    {
        $disk = $this->storageDisk();
        $path = $file->storeAs($folder, $filename, $disk);

        return $this->storageUrl($disk, $path);
    }

    /**
     * Build the correct public URL for a stored file.
     *
     * S3/R2:  uses AWS_URL env var (e.g. https://pub-xxx.r2.dev) + path
     * Local:  uses /storage/ prefix
     */
    protected function storageUrl(string $disk, string $path): string
    {
        if ($disk === 's3') {
            // R2/S3 public URL = AWS_URL + '/' + path
            $baseUrl = rtrim(config('filesystems.disks.s3.url', env('AWS_URL', '')), '/');
            return $baseUrl . '/' . ltrim($path, '/');
        }

        // Local public disk
        return '/storage/' . ltrim($path, '/');
    }

    /**
     * Delete a file from storage.
     * Handles: S3/R2 full URLs, local /storage/ paths, null.
     */
    protected function deleteStorageFile(?string $url): void
    {
        if (!$url) return;

        $disk = $this->storageDisk();

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            // Extract storage key from full URL
            if ($disk === 's3') {
                $baseUrl = rtrim(config('filesystems.disks.s3.url', env('AWS_URL', '')), '/');
                if ($baseUrl && str_starts_with($url, $baseUrl)) {
                    $key = ltrim(substr($url, strlen($baseUrl)), '/');
                    if ($key) Storage::disk('s3')->delete($key);
                }
            }
        } elseif (str_starts_with($url, '/storage/')) {
            // Legacy local storage path
            Storage::disk('public')->delete(str_replace('/storage/', '', $url));
        }
    }
}
