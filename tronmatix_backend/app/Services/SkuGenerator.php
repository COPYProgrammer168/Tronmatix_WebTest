<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Str;

/**
 * Auto-generates product SKUs.
 *
 * Format: {PREFIX}{5 RANDOM CHARS} — no separator, e.g. CPUA7BQP / RAM8F2QZ.
 *
 * The prefix is derived from the product's `category` string via a fixed map
 * (the `brand` column is empty across the catalogue — brand-like values such
 * as SECRETLAB / TTR RACING live in the category string). Any unmapped
 * category falls back to the first 4 letters of its first word, uppercased.
 *
 * The suffix is random (not sequential) so concurrent inserts can never race
 * on a shared counter — a collision is simply retried against the unique index.
 */
class SkuGenerator
{
    /** Length of the random suffix appended to the prefix. */
    public const RANDOM_LEN = 5;

    /** Alphanumeric pool for the random suffix — unambiguous, no 0/O/1/l. */
    private const POOL = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    /** Max attempts before giving up on a collision (astronomically unlikely). */
    private const MAX_ATTEMPTS = 50;

    /**
     * Category string → SKU prefix.
     * Keyed on the exact `category` value used in the DB (see SkuGenerator docs
     * for the live catalogue). Multi-size groups (MONITOR, PC BUILD) share one
     * prefix, so their sequences are not split by size.
     */
    public const PREFIX_MAP = [
        'CPU'            => 'CPU',
        'RAM'            => 'RAM',
        'VGA'            => 'VGA',
        'MAINBOARD'      => 'MB',
        'COOLING'        => 'COOL',
        'M2'             => 'M2',
        'CASE'           => 'CASE',
        'POWER SUPPLY'   => 'PSU',
        'FAN'            => 'FAN',
        'KEYBOARD'       => 'KBD',
        'MOUSE'          => 'MOU',
        'MOUSEPAD'       => 'MPD',
        'HEADSET'        => 'HDS',
        'EARPHONE'       => 'EAR',
        'SPEAKER'        => 'SPK',
        'MICROPHONE'     => 'MIC',
        'MONITOR STAND'  => 'MST',
        'ROUTER'         => 'RTR',
        'STRIMER SET'    => 'STS',
        'SECRETLAB'      => 'SECRET',
        'TTR RACING'     => 'TTR',
        'DX RACER'       => 'DXR',
        'ASUS'           => 'ASU',
        'FANTECH'        => 'FTK',
        'BEST PRICE'     => 'BP',
        // Multi-size groups share a single prefix.
        'MONITOR 25INCH' => 'MON',
        'MONITOR 27INCH' => 'MON',
        'MONITOR 32INCH' => 'MON',
        'MONITOR 34INCH' => 'MON',
        'MONITOR 39INCH' => 'MON',
        'MONITOR 42INCH' => 'MON',
        'MONITOR 45INCH' => 'MON',
        'MONITOR 48INCH' => 'MON',
        'MONITOR 49INCH' => 'MON',
        'PC BUILD UNDER 1K' => 'PB',
        'PC BUILD UNDER 2K' => 'PB',
        'PC BUILD UNDER 3K' => 'PB',
        'PC BUILD UNDER 4K' => 'PB',
        'PC BUILD UNDER 5K' => 'PB',
        'PC BUILD 5K UP'    => 'PB',
    ];

    /**
     * Resolve the prefix for a category string.
     */
    public static function prefix(string $category): string
    {
        $key = strtoupper(trim($category));

        if (isset(self::PREFIX_MAP[$key])) {
            return self::PREFIX_MAP[$key];
        }

        // Fallback: first 4 letters of the first word, uppercased.
        $first = strtoupper(Str::of($key)->before(' ')->value() ?: $key);

        return substr($first, 0, 4);
    }

    /**
     * Generate a fresh, unique SKU for a category.
     *
     * Loops up to MAX_ATTEMPTS, re-rolling the random suffix until it is not
     * already taken. No transaction/lock is needed — a random suffix has no
     * dependency on prior rows, so concurrent inserts can never collide on a
     * shared counter (the unique index on `sku` is the final backstop).
     */
    public static function generate(string $category): string
    {
        $prefix = self::prefix($category);

        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $sku = $prefix . self::randomSuffix();

            if (! Product::where('sku', $sku)->exists()) {
                return $sku;
            }
        }

        throw new \RuntimeException("Could not allocate a unique SKU for category \"{$category}\" after " . self::MAX_ATTEMPTS . ' attempts.');
    }

    /**
     * Build a preview SKU for a category — same prefix, deterministic placeholder
     * suffix. Used by the create form so staff can see the shape before saving;
     * the real SKU is generated server-side on save.
     */
    public static function preview(string $category): string
    {
        return self::prefix($category) . str_repeat('X', self::RANDOM_LEN);
    }

    /**
     * Random uppercase-alphanumeric suffix (no 0/O/1/l to avoid confusion).
     */
    private static function randomSuffix(): string
    {
        $out = '';
        $max = strlen(self::POOL) - 1;

        for ($i = 0; $i < self::RANDOM_LEN; $i++) {
            $out .= self::POOL[random_int(0, $max)];
        }

        return $out;
    }
}
