<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    // ── Show settings page ────────────────────────────────────────────────────
    public function show()
    {
        // FIX [3]: AdminSetting::allMap() — camelCase, not all_map()
        $settings = AdminSetting::allMap();
        $counts = $this->liveCounts($settings);

        return view('dashboard.settings', compact('settings', 'counts'));
    }

    // ── Save settings ─────────────────────────────────────────────────────────
    public function update(Request $request)
    {
        $request->validate([
            'notif_low_stock_threshold' => 'nullable|integer|min:1|max:999',
            'order_auto_cancel_hours' => 'nullable|integer|min:0|max:720',
            'store_name' => 'nullable|string|max:100',
            'store_currency' => 'nullable|string|max:10',
            'dashboard_rows_per_page' => 'nullable|integer|min:5|max:200',
            'products_per_page' => 'nullable|integer|min:4|max:100',
            'vip_threshold' => 'nullable|numeric|min:0',
        ]);

        $boolKeys = [
            'notif_low_stock', 'notif_new_order', 'notif_pending_payment',
            'notif_qr_confirmed', 'notif_delivery_confirm',
            'order_auto_confirm_cash', 'store_open',
        ];
        $textKeys = [
            'notif_low_stock_threshold', 'order_auto_cancel_hours',
            'store_name', 'store_currency',
            'dashboard_rows_per_page', 'products_per_page',
            'vip_threshold',  // FIX [2]: included in save
        ];

        $data = [];
        foreach ($boolKeys as $key) {
            $data[$key] = $request->has($key) ? '1' : '0';
        }
        foreach ($textKeys as $key) {
            if ($request->has($key)) {
                $data[$key] = $request->input($key);
            }
        }

        AdminSetting::saveMany($data);

        return redirect()->route('dashboard.settings')->with('success', 'Settings saved ✓');
    }

    // ── Reset to defaults ─────────────────────────────────────────────────────
    public function reset()
    {
        // FIX [1]: delegate to AdminSetting::reset() — single source of truth
        // FIX [2]: AdminSetting::reset() now includes 'vip_threshold'
        AdminSetting::reset();

        return redirect()->route('dashboard.settings')->with('success', 'Settings reset to defaults ✓');
    }

    // ── Notifications JSON (polled by topbar bell) ────────────────────────────
    public function notifications()
    {
        // FIX [3]: allMap() not all_map()
        $settings = AdminSetting::allMap();
        $threshold = AdminSetting::int('notif_low_stock_threshold', 5);

        $alerts = [];

        if (AdminSetting::enabled('notif_low_stock')) {
            // FIX [4]: use Product::lowStock() scope instead of inline hardcoded query
            $count = Product::lowStock()->count();
            if ($count > 0) {
                $alerts[] = [
                    'type' => 'low_stock',
                    'icon' => '🟠',
                    'color' => '#F97316',
                    'title' => "{$count} Low Stock Product".($count > 1 ? 's' : ''),
                    'body' => "Stock at or below {$threshold} units",
                    'url' => route('dashboard.products'),
                ];
            }
        }

        if (AdminSetting::enabled('notif_new_order')) {
            $count = Order::where('status', 'pending')->whereDate('created_at', today())->count();
            if ($count > 0) {
                $alerts[] = [
                    'type' => 'new_order',
                    'icon' => '📦',
                    'color' => '#eab308',
                    'title' => "{$count} New Order".($count > 1 ? 's' : '').' Today',
                    'body' => 'Pending orders waiting for confirmation',
                    'url' => route('dashboard.orders', ['status' => 'pending']),
                ];
            }
        }

        if (AdminSetting::enabled('notif_pending_payment')) {
            $count = Order::where('payment_status', 'pending')
                ->where('payment_method', 'bakong')
                ->whereIn('status', ['pending', 'confirmed'])
                ->count();
            if ($count > 0) {
                $alerts[] = [
                    'type' => 'pending_payment',
                    'icon' => '📱',
                    'color' => '#3b82f6',
                    'title' => "{$count} Awaiting KHQR Payment",
                    'body' => 'ABA BAKONG payments not yet confirmed',
                    'url' => route('dashboard.orders'),
                ];
            }
        }

        if (AdminSetting::enabled('notif_qr_confirmed')) {
            $count = Order::where('payment_status', 'paid')
                ->where('payment_method', 'bakong')
                ->whereDate('updated_at', today())
                ->count();
            if ($count > 0) {
                $alerts[] = [
                    'type' => 'qr_confirmed',
                    'icon' => '✅',
                    'color' => '#22c55e',
                    'title' => "{$count} KHQR Payment".($count > 1 ? 's' : '').' Confirmed Today',
                    'body' => 'ABA BAKONG auto-confirmed today',
                    'url' => route('dashboard.orders'),
                ];
            }
        }

        if (AdminSetting::enabled('notif_delivery_confirm')) {
            $count = Order::where('status', 'delivered')
                ->whereDate('delivery_confirmed_at', today())
                ->count();
            if ($count > 0) {
                $alerts[] = [
                    'type' => 'delivery',
                    'icon' => '🚚',
                    'color' => '#a78bfa',
                    'title' => "{$count} Deliver".($count > 1 ? 'ies' : 'y').' Confirmed Today',
                    'body' => 'Orders marked as delivered',
                    'url' => route('dashboard.orders', ['status' => 'delivered']),
                ];
            }
        }

        return response()->json(['count' => count($alerts), 'alerts' => $alerts]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────
    private function liveCounts(array $settings): array
    {
        $threshold = (int) ($settings['notif_low_stock_threshold'] ?? 5);

        return [
            'low_stock' => Product::lowStock()->count(), // FIX [4]
            'pending_orders' => Order::where('status', 'pending')->whereDate('created_at', today())->count(),
            'pending_payment' => Order::where('payment_status', 'pending')->where('payment_method', 'bakong')->count(),
            'qr_confirmed' => Order::where('payment_status', 'paid')->where('payment_method', 'bakong')->whereDate('updated_at', today())->count(),
            'delivered_today' => Order::where('status', 'delivered')->whereDate('delivery_confirmed_at', today())->count(),
        ];
    }

    public function updatePermissions(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        abort_unless(
            in_array($admin->role, ['admin', 'superadmin']),
            403,
            'Access denied.'
        );

        $roles = ['admin', 'editor', 'viewer'];
        $features = ['dashboard', 'products', 'orders', 'orders_edit', 'users', 'discounts', 'settings', 'staff'];

        $permsToSave = [];

        foreach ($roles as $role) {
            foreach ($features as $feature) {
                $key = "perm_{$role}_{$feature}";
                $lockedOn = $role === 'admin' && in_array($feature, ['settings', 'staff', 'orders_edit', 'users']);

                $permsToSave[$key] = $lockedOn
                    ? '1'
                    : ($request->has($key) ? '1' : '0');
            }
        }

        AdminSetting::saveMany($permsToSave);

        return redirect()->route('dashboard.settings')
            ->with('success', 'Permissions saved successfully.');
    }
}
