<?php

// app/Models/AdminSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

// use Illuminate\Support\Facades\DB;

class AdminSetting extends Model
{
    protected $fillable = ['key', 'value'];

    private const CACHE_KEY = 'admin_settings';

    private const CACHE_TTL = 300; // 5 minutes

    // ── Static getters ────────────────────────────────────────────────────────

    /** Get a setting value, with optional default */
    public static function get(string $key, mixed $default = null): mixed
    {
        $map = static::allMap();

        return $map[$key] ?? $default;
    }

    /** Typed int getter */
    public static function int(string $key, int $default = 0): int
    {
        return (int) static::get($key, $default);
    }

    /** Typed string getter */
    public static function str(string $key, string $default = ''): string
    {
        return (string) static::get($key, $default);
    }

    /** Boolean check — '1', 'true', 1, true all return true */
    public static function enabled(string $key): bool
    {
        return filter_var(static::get($key, false), FILTER_VALIDATE_BOOLEAN);
    }

    /** All settings as key→value array (cached) */
    public static function allMap(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, fn () => static::pluck('value', 'key')->toArray()
        );
    }

    // ── Static writers ────────────────────────────────────────────────────────

    /** Save multiple keys at once, then bust cache */
    public static function saveMany(array $data): void
    {
        foreach ($data as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget(self::CACHE_KEY);
    }

    // FIX [1]: reset() — SettingsController calls AdminSetting::reset()
    /** Restore all settings to factory defaults */
    public static function reset(): void
    {
        $defaults = [
            'notif_low_stock' => '1',
            'notif_low_stock_threshold' => '5',
            'notif_new_order' => '1',
            'notif_pending_payment' => '1',
            'notif_qr_confirmed' => '1',
            'notif_delivery_confirm' => '1',
            'order_auto_confirm_cash' => '0',
            'order_auto_cancel_hours' => '0',
            'store_name' => 'Tronmatix Computer',
            'store_currency' => 'USD',
            'store_open' => '1',
            'dashboard_rows_per_page' => '20',
            'products_per_page' => '12',
            'vip_threshold' => '1000',
        ];

        static::saveMany($defaults); // busts cache internally
    }

    /** Bust the settings cache (call after direct DB updates) */
    public static function bustCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
