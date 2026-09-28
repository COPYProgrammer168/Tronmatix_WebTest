<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\MarqueeMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\StaffRequest;
use App\Models\User;
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
        $marqueeMessages = MarqueeMessage::orderBy('order')->get(['id','route','text_en','text_kh','is_active','order']);

        return view('dashboard.settings', compact('settings', 'counts', 'marqueeMessages'));
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
        $alerts = $this->alertList();

        // ── Respect dismissed ("CLEAR ALL") alerts ─────────────────────────────
        // clearNotifications() persisted a JSON list of dismissed alert ids.
        // New alerts (different ids) still appear; dismissed ones stay hidden
        // until their underlying item changes (e.g. a new order id).
        $dismissed = json_decode((string) AdminSetting::get('notif_dismissed', '[]'), true) ?: [];

        if (! empty($dismissed)) {
            $alerts = array_values(array_filter($alerts, fn ($a) => ! in_array($a['id'] ?? '', $dismissed, true)));
        }

        return response()->json(['count' => count($alerts), 'alerts' => $alerts]);
    }

    /**
     * POST /dashboard/notifications/clear
     *
     * Dismisses the currently-polled notification bell alerts ("CLEAR ALL").
     * The current alert ids are persisted as a dismissed-set; notifications()
     * filters them out so the bell empties and stops nagging. Genuinely new
     * alerts (fresh order/request ids) still surface.
     */
    public function clearNotifications()
    {
        // Capture the current alert ids, merge with any previously dismissed,
        // cap the list so it never grows unbounded.
        $current = collect($this->alertList())->pluck('id')->filter()->values()->all();

        $previous = json_decode((string) AdminSetting::get('notif_dismissed', '[]'), true) ?: [];
        $dismissed = array_slice(array_values(array_unique(array_merge($previous, $current))), -200);

        \App\Models\AdminSetting::saveMany([
            'notif_dismissed' => json_encode($dismissed),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notifications cleared.',
        ]);
    }

    /**
     * Build the live alert array (shared by notifications() and clearNotifications()).
     * Returns the same $alerts list so "clear" dismisses exactly what the bell shows.
     */
    private function alertList(): array
    {
        $settings  = AdminSetting::allMap();
        $threshold = AdminSetting::int('notif_low_stock_threshold', 5);
        $admin     = Auth::guard('admin')->user();
        $isSuperAdmin = $admin && $admin->role === 'superadmin';

        $alerts = [];

        if ($isSuperAdmin) {
            $pendingRequests = StaffRequest::pending()->orderByDesc('created_at')->take(10)->get();
            foreach ($pendingRequests as $req) {
                $alerts[] = [
                    'id' => 'staff_request_' . $req->id, 'type' => 'staff_request',
                    'icon' => '👤', 'color' => '#a78bfa',
                    'font' => 'ACCESS REQUEST — ' . strtoupper($req->name),
                    'title' => 'ACCESS REQUEST — ' . strtoupper($req->name),
                    'body' => $req->email . ' · wants ' . strtoupper($req->requested_role) . ' · ' . $req->created_at->diffForHumans(),
                    'url' => route('dashboard.staff'),
                    'request_id' => $req->id, 'request_name' => $req->name,
                    'request_role' => $req->requested_role, 'request_email' => $req->email,
                    'request_message' => $req->message, 'actionable' => true,
                ];
            }
        }

        if (AdminSetting::enabled('notif_low_stock')) {
            $count = Product::lowStock()->count();
            if ($count > 0) {
                $alerts[] = [
                    'id' => 'low_stock_' . $count, 'type' => 'low_stock', 'icon' => '🟠', 'color' => '#F97316',
                    'font' => "{$count} Low Stock Product".($count > 1 ? 's' : ''),
                    'title' => "{$count} Low Stock Product".($count > 1 ? 's' : ''),
                    'body' => "Stock at or below {$threshold} units", 'url' => route('dashboard.products'),
                ];
            }
        }

        if (AdminSetting::enabled('notif_new_order')) {
            $newOrders = Order::where('status', 'pending')->where('created_at', '>=', now()->subMinutes(30))
                ->orderByDesc('created_at')->take(5)->get();
            foreach ($newOrders as $order) {
                $alerts[] = [
                    'id' => 'new_order_' . $order->id, 'type' => 'new_order', 'icon' => '🛒', 'color' => '#eab308',
                    'font' => 'NEW ORDER #' . ($order->order_id ?? $order->id),
                    'title' => 'NEW ORDER #' . ($order->order_id ?? $order->id),
                    'body' => '$' . number_format($order->total, 2) . ' — ' . ($order->user->name ?? 'Guest') . ' · ' . $order->created_at->diffForHumans(),
                    'url' => route('dashboard.orders.show', $order->order_id),
                ];
            }
            $todayCount = Order::where('status', 'pending')->whereDate('created_at', today())->count();
            if ($todayCount > 0 && $newOrders->isEmpty()) {
                $alerts[] = [
                    'id' => 'pending_today_' . today()->format('Ymd'), 'type' => 'new_order', 'icon' => '📦', 'color' => '#eab308',
                    'font' => "{$todayCount} Pending Order".($todayCount > 1 ? 's' : '').' Today',
                    'title' => "{$todayCount} Pending Order".($todayCount > 1 ? 's' : '').' Today',
                    'body' => 'Waiting for confirmation', 'url' => route('dashboard.orders', ['status' => 'pending']),
                ];
            }
        }

        if (AdminSetting::enabled('notif_pending_payment')) {
            $count = Order::where('payment_status', 'pending')->where('payment_method', 'bakong')
                ->whereIn('status', ['pending', 'confirmed'])->count();
            if ($count > 0) {
                $alerts[] = [
                    'id' => 'pending_payment_' . $count, 'type' => 'pending_payment', 'icon' => '📱', 'color' => '#3b82f6',
                    'font' => "{$count} Awaiting KHQR Payment", 'title' => "{$count} Awaiting KHQR Payment",
                    'body' => 'ABA BAKONG payments not yet confirmed', 'url' => route('dashboard.orders'),
                ];
            }
            $manualPending = Order::where('payment_status', 'manual_pending')->where('updated_at', '>=', now()->subMinutes(60))
                ->orderByDesc('updated_at')->take(3)->get();
            foreach ($manualPending as $order) {
                $alerts[] = [
                    'id' => 'manual_' . $order->id, 'type' => 'manual_payment', 'icon' => '⏳', 'color' => '#f59e0b',
                    'font' => 'MANUAL PAYMENT #' . ($order->order_id ?? $order->id),
                    'title' => 'MANUAL PAYMENT #' . ($order->order_id ?? $order->id),
                    'body' => '$' . number_format($order->total, 2) . ' — ' . ($order->user->name ?? 'Guest') . ' claims payment sent',
                    'url' => route('dashboard.orders.show', $order->order_id),
                ];
            }
        }

        if (AdminSetting::enabled('notif_qr_confirmed')) {
            $paidOrders = Order::where('payment_status', 'paid')->where('payment_method', 'bakong')
                ->where('updated_at', '>=', now()->subMinutes(30))->orderByDesc('updated_at')->take(5)->get();
            foreach ($paidOrders as $order) {
                $alerts[] = [
                    'id' => 'paid_' . $order->id, 'type' => 'qr_confirmed', 'icon' => '💳', 'color' => '#22c55e',
                    'font' => 'PAYMENT CONFIRMED #' . ($order->order_id ?? $order->id),
                    'title' => 'PAYMENT CONFIRMED #' . ($order->order_id ?? $order->id),
                    'body' => '$' . number_format($order->total, 2) . ' paid via KHQR · ' . $order->updated_at->diffForHumans(),
                    'url' => route('dashboard.orders.show', $order->order_id),
                ];
            }
            $todayPaid = Order::where('payment_status', 'paid')->where('payment_method', 'bakong')->whereDate('updated_at', today())->count();
            if ($todayPaid > 0 && $paidOrders->isEmpty()) {
                $alerts[] = [
                    'id' => 'qr_today_' . today()->format('Ymd'), 'type' => 'qr_confirmed', 'icon' => '✅', 'color' => '#22c55e',
                    'font' => "{$todayPaid} KHQR Payment".($todayPaid > 1 ? 's' : '').' Confirmed Today',
                    'title' => "{$todayPaid} KHQR Payment".($todayPaid > 1 ? 's' : '').' Confirmed Today',
                    'body' => 'ABA BAKONG auto-confirmed', 'url' => route('dashboard.orders'),
                ];
            }
        }

        $cancelledOrders = Order::where('status', 'cancelled')->where('updated_at', '>=', now()->subMinutes(60))
            ->orderByDesc('updated_at')->take(3)->get();
        foreach ($cancelledOrders as $order) {
            $alerts[] = [
                'id' => 'cancelled_' . $order->id, 'type' => 'cancelled', 'icon' => '❌', 'color' => '#ef4444',
                'font' => 'ORDER CANCELLED #' . ($order->order_id ?? $order->id),
                'title' => 'ORDER CANCELLED #' . ($order->order_id ?? $order->id),
                'body' => '$' . number_format($order->total, 2) . ' — ' . ($order->user->name ?? 'Guest') . ' · ' . $order->updated_at->diffForHumans(),
                'url' => route('dashboard.orders'),
            ];
        }

        if (AdminSetting::enabled('notif_delivery_confirm')) {
            $count = Order::where('status', 'delivered')->whereDate('delivery_confirmed_at', today())->count();
            if ($count > 0) {
                $alerts[] = [
                    'id' => 'delivery_' . today()->format('Ymd'), 'type' => 'delivery', 'icon' => '🚚', 'color' => '#a78bfa',
                    'font' => "{$count} Deliver".($count > 1 ? 'ies' : 'y').' Confirmed Today',
                    'title' => "{$count} Deliver".($count > 1 ? 'ies' : 'y').' Confirmed Today',
                    'body' => 'Orders marked as delivered', 'url' => route('dashboard.orders', ['status' => 'delivered']),
                ];
            }
        }

        return $alerts;
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

    // ── Update role-permission matrix ─────────────────────────────────────────
    public function updatePermissions(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        abort_unless(
            in_array($admin->role, ['admin', 'superadmin']),
            403,
            'Access denied.'
        );

        $roles     = \App\Models\AdminSetting::getAllRoleKeys();
        $features  = \App\Models\AdminSetting::getAllFeatureKeys();
        $roleMeta  = \App\Models\Role::all()->keyBy('key');

        $permsToSave = [];

        foreach ($roles as $role) {
            foreach ($features as $feature) {
                $key = "perm_{$role}_{$feature}";

                // Locked-on: admin role always has these sensitive features
                $lockedOn = $role === 'admin'
                    && in_array($feature, ['settings', 'staff', 'orders_edit', 'users']);

                // Locked-off: role-specific forbidden features from DB
                $lockedOff = false;
                if ($roleMeta->has($role)) {
                    // Coerce to array — a double-encoded row reads back as the
                    // string "[]", which would crash in_array() below.
                    $forbidden = $roleMeta[$role]->forbidden_features;
                    $forbidden = is_array($forbidden) ? $forbidden : [];
                    if (in_array($feature, $forbidden, true)) {
                        $lockedOff = true;
                    }
                }

                if ($lockedOn || $lockedOff) {
                    $permsToSave[$key] = $lockedOn ? '1' : '0';
                } elseif ($request->has($key)) {
                    $permsToSave[$key] = '1';
                } else {
                    $permsToSave[$key] = '0';
                }
            }
        }

        AdminSetting::saveMany($permsToSave);

        return redirect()->route('dashboard.settings')
            ->with('success', 'Permissions saved successfully.');
    }

    // ── Roles CRUD ─────────────────────────────────────────────────────────────

    /** POST /dashboard/settings/roles — superadmin only */
    public function storeRole(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && $admin->role === 'superadmin', 403, 'Superadmin only.');

        $data = $request->validate([
            'key'         => 'required|string|max:50|unique:roles,key',
            'label'       => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'color'       => 'nullable|string|max:20',
            'icon'        => 'required|string|max:20',
            'sort_order'  => 'nullable|integer|min:0',
            'is_staff_portal' => 'nullable',
        ]);

        \Illuminate\Support\Facades\Log::info('Store Role Data:', $data);

        $data['sort_order']      = (int) ($data['sort_order'] ?? 0);
        $data['color']           = $data['color'] ?? '#6b7280';
        $data['icon']            = $data['icon'] ?? '❓';
        $data['description']     = $data['description'] ?? null;
        
        // Convert to actual boolean
        $data['is_staff_portal'] = $request->has('is_staff_portal');
        $data['is_locked']       = false; 
        
        // The Role model casts these as `array`, which JSON-encodes on write.
        $data['locked_features']    = [];
        $data['forbidden_features'] = [];

        $role = \App\Models\Role::create($data);
        \Illuminate\Support\Facades\Log::info('Role Created:', ['id' => $role->id]);

        return redirect()->route('dashboard.settings')
            ->with('success', 'Role added successfully.');
    }

    /** PUT /dashboard/settings/roles/{role} — superadmin only */
    public function updateRole(Request $request, $id)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && $admin->role === 'superadmin', 403, 'Superadmin only.');

        $role = \App\Models\Role::findOrFail($id);

        if ($role->key === 'superadmin') {
            abort(403, 'Cannot modify the superadmin role.');
        }

        $data = $request->validate([
            'label'       => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'color'       => 'nullable|string|max:20',
            'icon'        => 'nullable|string|max:200',
            'sort_order'  => 'nullable|integer|min:0',
            'is_staff_portal' => 'nullable|boolean',
            'locked_features'    => 'nullable|array',
            'forbidden_features' => 'nullable|array',
        ]);

        $update = [
            'label'        => $data['label'],
            'description'  => $data['description'] ?? $role->description,
            'color'        => $data['color'] ?? $role->color,
            'icon'         => $data['icon'] ?? $role->icon,
            'sort_order'   => (int) ($data['sort_order'] ?? $role->sort_order),
            // Postgres boolean column — string literal, not PHP bool (see storeRole).
            'is_staff_portal' => $request->boolean('is_staff_portal', (bool) $role->is_staff_portal) ? 'true' : 'false',
        ];

        if (isset($data['locked_features'])) {
            // Role's `array` cast JSON-encodes on write — pass the raw array,
            // never a pre-encoded string (would double-encode, like storeRole).
            $update['locked_features'] = array_values($data['locked_features']);
        }
        if (isset($data['forbidden_features'])) {
            $update['forbidden_features'] = array_values($data['forbidden_features']);
        }

        $role->update($update);

        return redirect()->route('dashboard.settings')
            ->with('success', 'Role updated successfully.');
    }

    /** DELETE /dashboard/settings/roles/{role} — superadmin only */
    public function destroyRole($id)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && $admin->role === 'superadmin', 403, 'Superadmin only.');

        $role = \App\Models\Role::findOrFail($id);

        if ($role->key === 'superadmin' || $role->is_locked) {
            abort(403, 'Cannot delete this role.');
        }

        $roleKey = $role->key;

        // Remove all perm_{roleKey}_* settings from admin_settings
        $features = \App\Models\AdminSetting::getAllFeatureKeys();
        $permKeys = array_map(fn ($f) => "perm_{$roleKey}_{$f}", $features);
        \App\Models\AdminSetting::whereIn('key', $permKeys)->delete();
        \App\Models\AdminSetting::bustCache();

        $role->delete();

        return redirect()->route('dashboard.settings')
            ->with('success', 'Role deleted successfully.');
    }

    // ── Features CRUD ──────────────────────────────────────────────────────────

    /** POST /dashboard/settings/features — superadmin only */
    public function storeFeature(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && $admin->role === 'superadmin', 403, 'Superadmin only.');

        $data = $request->validate([
            'key'        => 'required|string|max:50|unique:features,key',
            'label'      => 'required|string|max:100',
            'icon'       => 'nullable|string|max:200',
            'category'   => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['icon']       = $data['icon'] ?? '📄';

        \App\Models\Feature::create($data);

        return redirect()->route('dashboard.settings')
            ->with('success', 'Feature created successfully.');
    }

    /** PUT /dashboard/settings/features/{feature} — superadmin only */
    public function updateFeature(Request $request, $id)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && $admin->role === 'superadmin', 403, 'Superadmin only.');

        $feature = \App\Models\Feature::findOrFail($id);

        $data = $request->validate([
            'label'      => 'required|string|max:100',
            'icon'       => 'nullable|string|max:200',
            'category'   => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $feature->update([
            'label'      => $data['label'],
            'icon'       => $data['icon'] ?? $feature->icon,
            'category'   => $data['category'] ?? $feature->category,
            'sort_order' => (int) ($data['sort_order'] ?? $feature->sort_order),
        ]);

        return redirect()->route('dashboard.settings')
            ->with('success', 'Feature updated successfully.');
    }

    /** DELETE /dashboard/settings/features/{feature} — superadmin only */
    public function destroyFeature($id)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && $admin->role === 'superadmin', 403, 'Superadmin only.');

        $feature = \App\Models\Feature::findOrFail($id);
        $featureKey = $feature->key;

        // Remove all perm_*_{featureKey} settings from admin_settings
        $roles = \App\Models\AdminSetting::getAllRoleKeys();
        $permKeys = array_map(fn ($r) => "perm_{$r}_{$featureKey}", $roles);
        \App\Models\AdminSetting::whereIn('key', $permKeys)->delete();
        \App\Models\AdminSetting::bustCache();

        $feature->delete();

        return redirect()->route('dashboard.settings')
            ->with('success', 'Feature deleted successfully.');
    }

    // ── Notifications JSON (polled by topbar bell) ────────────────────────────

    public function resetVipRoles(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        abort_unless(
            in_array($admin->role, ['admin', 'superadmin']),
            403,
            'Access denied.'
        );

        $vipGoal = (float) AdminSetting::get('vip_threshold', 5000);

        $updated = User::where('role', 'vip')
            ->where(function ($query) use ($vipGoal) {
                // No non-cancelled orders at all
                $query->whereDoesntHave('orders', function ($q) {
                    $q->whereNotIn('status', [Order::STATUS_CANCELLED]);
                })
                // Total spent from non-cancelled orders < threshold
                ->orWhereIn('id', function ($sub) use ($vipGoal) {
                    $sub->select('user_id')
                        ->from('orders')
                        ->whereNotIn('status', [Order::STATUS_CANCELLED])
                        ->whereColumn('orders.user_id', 'users.id')
                        ->groupBy('user_id')
                        ->havingRaw('SUM(total) < ?', [$vipGoal]);
                });
            })
            ->update(['role' => 'customer']);

        return redirect()->route('dashboard.settings')
            ->with('success', "VIP roles reset: {$updated} users demoted to customer.");
    }

    // ── Marquee messages CRUD ──────────────────────────────────────────────────

    public function marquees(Request $request)
    {
        $admin = Auth::guard('admin')->user() ?? Auth::guard('staff')->user();
        abort_unless($admin && in_array($admin->role, ['admin','superadmin']), 403);

        $messages = \App\Models\MarqueeMessage::orderBy('order')->get(['id','route','text_en','text_kh','is_active','order']);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $messages]);
        }

        return view('dashboard.settings', array_merge(
            $this->show()->getData(),
            ['marqueeMessages' => $messages]
        ));
    }

    public function storeMarquee(Request $request)
    {
        $admin = Auth::guard('admin')->user() ?? Auth::guard('staff')->user();
        abort_unless($admin && in_array($admin->role, ['admin','superadmin']), 403);

        $request->validate([
            'route'      => 'nullable|string|max:100',
            'text_en'    => 'required|string|max:500',
            'text_kh'    => 'required|string|max:500',
            'is_active'  => 'boolean',
            'order'      => 'nullable|integer|min:0',
        ]);

        \App\Models\MarqueeMessage::create([
            'route'     => $request->input('route') ?: null,
            'text_en'   => $request->input('text_en'),
            'text_kh'   => $request->input('text_kh'),
            'is_active' => $request->boolean('is_active', true),
            'order'     => (int) ($request->input('order', 0)),
        ]);

        return redirect()->route('dashboard.settings.marquees')->with('success', 'Marquee message created.');
    }

    public function updateMarquee(Request $request, $id)
    {
        $admin = Auth::guard('admin')->user() ?? Auth::guard('staff')->user();
        abort_unless($admin && in_array($admin->role, ['admin','superadmin']), 403);

        $message = \App\Models\MarqueeMessage::findOrFail($id);

        $request->validate([
            'route'      => 'nullable|string|max:100',
            'text_en'    => 'required|string|max:500',
            'text_kh'    => 'required|string|max:500',
            'is_active'  => 'boolean',
            'order'      => 'nullable|integer|min:0',
        ]);

        $message->update([
            'route'     => $request->input('route') ?: null,
            'text_en'   => $request->input('text_en'),
            'text_kh'   => $request->input('text_kh'),
            'is_active' => $request->boolean('is_active', true),
            'order'     => (int) ($request->input('order', 0)),
        ]);

        return redirect()->route('dashboard.settings.marquees')->with('success', 'Marquee message updated.');
    }

    public function destroyMarquee(Request $request, $id)
    {
        $admin = Auth::guard('admin')->user() ?? Auth::guard('staff')->user();
        abort_unless($admin && in_array($admin->role, ['admin','superadmin']), 403);

        $message = \App\Models\MarqueeMessage::findOrFail($id);
        $message->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('dashboard.settings.marquees')->with('success', 'Marquee message deleted.');
    }
}