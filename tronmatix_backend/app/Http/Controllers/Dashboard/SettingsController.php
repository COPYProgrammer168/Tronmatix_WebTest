<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\Order;
use App\Models\Product;
use App\Models\StaffRequest;
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
        $settings  = AdminSetting::allMap();
        $threshold = AdminSetting::int('notif_low_stock_threshold', 5);
        $admin     = Auth::guard('admin')->user();
        $isSuperAdmin = $admin && $admin->role === 'superadmin';

        $alerts = [];

        // ── Staff access requests — superadmin only ───────────────────────────
        if ($isSuperAdmin) {
            $pendingRequests = StaffRequest::pending()
                ->orderByDesc('created_at')
                ->take(10)
                ->get();

            foreach ($pendingRequests as $req) {
                $alerts[] = [
                    'id'              => 'staff_request_' . $req->id,
                    'type'            => 'staff_request',
                    'icon'            => '👤',
                    'color'           => '#a78bfa',
                    'title'           => 'ACCESS REQUEST — ' . strtoupper($req->name),
                    'body'            => $req->email . ' · wants ' . strtoupper($req->requested_role) . ' · ' . $req->created_at->diffForHumans(),
                    'url'             => route('dashboard.staff'),
                    'request_id'      => $req->id,
                    'request_name'    => $req->name,
                    'request_role'    => $req->requested_role,
                    'request_email'   => $req->email,
                    'request_message' => $req->message,
                    'actionable'      => true,
                ];
            }
        }

        if (AdminSetting::enabled('notif_low_stock')) {
            $count = Product::lowStock()->count();
            if ($count > 0) {
                $alerts[] = [
                    'id'    => 'low_stock_' . $count,
                    'type'  => 'low_stock',
                    'icon'  => '🟠',
                    'color' => '#F97316',
                    'title' => "{$count} Low Stock Product".($count > 1 ? 's' : ''),
                    'body'  => "Stock at or below {$threshold} units",
                    'url'   => route('dashboard.products'),
                ];
            }
        }

        if (AdminSetting::enabled('notif_new_order')) {
            $newOrders = Order::where('status', 'pending')
                ->where('created_at', '>=', now()->subMinutes(30))
                ->orderByDesc('created_at')
                ->take(5)->get();

            foreach ($newOrders as $order) {
                $alerts[] = [
                    'id'    => 'new_order_' . $order->id,
                    'type'  => 'new_order',
                    'icon'  => '🛒',
                    'color' => '#eab308',
                    'title' => 'NEW ORDER #' . ($order->order_id ?? $order->id),
                    'body'  => '$' . number_format($order->total, 2) . ' — ' . ($order->user->name ?? 'Guest') . ' · ' . $order->created_at->diffForHumans(),
                    'url'   => route('dashboard.orders.show', $order->id),
                ];
            }

            $todayCount = Order::where('status', 'pending')->whereDate('created_at', today())->count();
            if ($todayCount > 0 && $newOrders->isEmpty()) {
                $alerts[] = [
                    'id'    => 'pending_today_' . today()->format('Ymd'),
                    'type'  => 'new_order',
                    'icon'  => '📦',
                    'color' => '#eab308',
                    'title' => "{$todayCount} Pending Order".($todayCount > 1 ? 's' : '').' Today',
                    'body'  => 'Waiting for confirmation',
                    'url'   => route('dashboard.orders', ['status' => 'pending']),
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
                    'id'    => 'pending_payment_' . $count,
                    'type'  => 'pending_payment',
                    'icon'  => '📱',
                    'color' => '#3b82f6',
                    'title' => "{$count} Awaiting KHQR Payment",
                    'body'  => 'ABA BAKONG payments not yet confirmed',
                    'url'   => route('dashboard.orders'),
                ];
            }

            $manualPending = Order::where('payment_status', 'manual_pending')
                ->where('updated_at', '>=', now()->subMinutes(60))
                ->orderByDesc('updated_at')->take(3)->get();
            foreach ($manualPending as $order) {
                $alerts[] = [
                    'id'    => 'manual_' . $order->id,
                    'type'  => 'manual_payment',
                    'icon'  => '⏳',
                    'color' => '#f59e0b',
                    'title' => 'MANUAL PAYMENT #' . ($order->order_id ?? $order->id),
                    'body'  => '$' . number_format($order->total, 2) . ' — ' . ($order->user->name ?? 'Guest') . ' claims payment sent',
                    'url'   => route('dashboard.orders.show', $order->id),
                ];
            }
        }

        if (AdminSetting::enabled('notif_qr_confirmed')) {
            $paidOrders = Order::where('payment_status', 'paid')
                ->where('payment_method', 'bakong')
                ->where('updated_at', '>=', now()->subMinutes(30))
                ->orderByDesc('updated_at')->take(5)->get();

            foreach ($paidOrders as $order) {
                $alerts[] = [
                    'id'    => 'paid_' . $order->id,
                    'type'  => 'qr_confirmed',
                    'icon'  => '💳',
                    'color' => '#22c55e',
                    'title' => 'PAYMENT CONFIRMED #' . ($order->order_id ?? $order->id),
                    'body'  => '$' . number_format($order->total, 2) . ' paid via KHQR · ' . $order->updated_at->diffForHumans(),
                    'url'   => route('dashboard.orders.show', $order->id),
                ];
            }

            $todayPaid = Order::where('payment_status', 'paid')->where('payment_method', 'bakong')->whereDate('updated_at', today())->count();
            if ($todayPaid > 0 && $paidOrders->isEmpty()) {
                $alerts[] = [
                    'id'    => 'qr_today_' . today()->format('Ymd'),
                    'type'  => 'qr_confirmed',
                    'icon'  => '✅',
                    'color' => '#22c55e',
                    'title' => "{$todayPaid} KHQR Payment".($todayPaid > 1 ? 's' : '').' Confirmed Today',
                    'body'  => 'ABA BAKONG auto-confirmed',
                    'url'   => route('dashboard.orders'),
                ];
            }
        }

        // Cancelled orders (last 60 min) — always show
        $cancelledOrders = Order::where('status', 'cancelled')
            ->where('updated_at', '>=', now()->subMinutes(60))
            ->orderByDesc('updated_at')->take(3)->get();
        foreach ($cancelledOrders as $order) {
            $alerts[] = [
                'id'    => 'cancelled_' . $order->id,
                'type'  => 'cancelled',
                'icon'  => '❌',
                'color' => '#ef4444',
                'title' => 'ORDER CANCELLED #' . ($order->order_id ?? $order->id),
                'body'  => '$' . number_format($order->total, 2) . ' — ' . ($order->user->name ?? 'Guest') . ' · ' . $order->updated_at->diffForHumans(),
                'url'   => route('dashboard.orders'),
            ];
        }

        if (AdminSetting::enabled('notif_delivery_confirm')) {
            $count = Order::where('status', 'delivered')
                ->whereDate('delivery_confirmed_at', today())
                ->count();
            if ($count > 0) {
                $alerts[] = [
                    'id'    => 'delivery_' . today()->format('Ymd'),
                    'type'  => 'delivery',
                    'icon'  => '🚚',
                    'color' => '#a78bfa',
                    'title' => "{$count} Deliver".($count > 1 ? 'ies' : 'y').' Confirmed Today',
                    'body'  => 'Orders marked as delivered',
                    'url'   => route('dashboard.orders', ['status' => 'delivered']),
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