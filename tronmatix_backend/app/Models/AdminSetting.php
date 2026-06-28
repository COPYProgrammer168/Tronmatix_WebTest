<?php

// app/Models/AdminSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AdminSetting extends Model
{
    protected $fillable = ['key', 'value'];

    private const CACHE_KEY = 'admin_settings';
    private const CACHE_TTL = 300; // 5 minutes

    // ── Permission Configuration ──────────────────────────────────────────────

    public static function getDefaults(): array
    {
        return [
            // admin — full access, locked in controller
            'admin_dashboard' => '1',
            'admin_products' => '1',
            'admin_orders' => '1',
            'admin_orders_edit' => '1',
            'admin_users' => '1',
            'admin_discounts' => '1',
            'admin_report' => '1',
            'admin_settings' => '1',
            'admin_staff' => '1',
            // editor — content only, no sensitive admin pages
            'editor_dashboard' => '1',
            'editor_products' => '1',
            'editor_orders' => '1',
            'editor_orders_edit' => '0',
            'editor_users' => '0',
            'editor_discounts' => '1',
            'editor_report' => '1',
            'editor_settings' => '0',
            'editor_staff' => '0',
            // seller — products, orders & discounts
            'seller_dashboard' => '1',
            'seller_products' => '1',
            'seller_orders' => '1',
            'seller_orders_edit' => '1',
            'seller_users' => '0',
            'seller_discounts' => '1',
            'seller_report' => '1',
            'seller_settings' => '0',
            'seller_staff' => '0',
            // delivery — orders view & edit only
            'delivery_dashboard' => '1',
            'delivery_products' => '0',
            'delivery_orders' => '1',
            'delivery_orders_edit' => '1',
            'delivery_users' => '0',
            'delivery_discounts' => '0',
            'delivery_report' => '0',
            'delivery_settings' => '0',
            'delivery_staff' => '0',
            // developer — read access, no admin-sensitive pages
            'developer_dashboard' => '1',
            'developer_products' => '1',
            'developer_orders' => '1',
            'developer_orders_edit' => '0',
            'developer_users' => '0',
            'developer_discounts' => '0',
            'developer_report' => '0',
            'developer_settings' => '0',
            'developer_staff' => '0',
        ];
    }

    public static function getRoleMeta(): array
    {
        return [
            'superadmin' => ['color' => '#F97316', 'icon' => '👑', 'label' => __('dashboard.roles.superadmin')],
            'admin' => ['color' => '#F97316', 'icon' => '🛡️', 'label' => __('dashboard.roles.admin')],
            'editor' => ['color' => '#3b82f6', 'icon' => '✏️', 'label' => __('dashboard.roles.editor')],
            'seller' => ['color' => '#10b981', 'icon' => '🏪', 'label' => __('dashboard.roles.seller')],
            'delivery' => ['color' => '#a855f7', 'icon' => '🚚', 'label' => __('dashboard.roles.delivery')],
            'developer' => ['color' => '#06b6d4', 'icon' => '💻', 'label' => __('dashboard.roles.developer')],
        ];
    }

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
        return Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL,
            fn() => static::pluck('value', 'key')->toArray()
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

        // Also reset all role permissions to factory defaults
        $permDefaults = [];
        foreach (static::getDefaults() as $key => $value) {
            $permDefaults["perm_{$key}"] = $value;
        }

        static::saveMany(array_merge($defaults, $permDefaults)); // busts cache internally
    }

    /** Bust the settings cache (call after direct DB updates) */
    public static function bustCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}