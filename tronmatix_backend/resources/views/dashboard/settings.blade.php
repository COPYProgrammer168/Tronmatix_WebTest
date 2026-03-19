@extends('dashboard.layout')
@section('title', 'SETTINGS')

@section('content')

@php
    $s = $settings;
    function son($settings, $key) { return ($settings[$key] ?? '0') === '1'; }
    function sval($settings, $key, $default = '') { return $settings[$key] ?? $default; }
@endphp

{{-- ── Save success popup ───────────────────────────────────────────────────── --}}
@if(session('success'))
<div id="save-popup" style="
    position:fixed; inset:0; z-index:9999; display:flex;
    align-items:center; justify-content:center; padding:20px;
    background:rgba(0,0,0,0.7); backdrop-filter:blur(8px);
    animation:sOverlayIn .2s ease;
">
    <div style="
        background:linear-gradient(145deg,#0b1f0e,#0a280c);
        border:1px solid rgba(34,197,94,0.3); border-radius:24px;
        padding:40px 36px; text-align:center; max-width:340px; width:100%;
        box-shadow:0 32px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(34,197,94,0.08);
        animation:sBoxIn .4s cubic-bezier(0.34,1.4,0.64,1);
        font-family:Rajdhani,sans-serif;
    ">
        <div style="
            width:86px; height:86px; border-radius:50%; margin:0 auto 20px;
            background:linear-gradient(135deg,#22c55e,#15803d);
            display:flex; align-items:center; justify-content:center; font-size:42px;
            box-shadow:0 0 48px rgba(34,197,94,0.5), 0 0 0 12px rgba(34,197,94,0.08);
            animation:sPopIn .55s cubic-bezier(0.34,1.56,0.64,1) .1s both;
        ">✓</div>
        <div style="font-size:26px; font-weight:900; color:#22c55e; letter-spacing:3px; margin-bottom:6px;">SAVED!</div>
        <div style="font-size:13px; color:rgba(255,255,255,0.45); margin-bottom:24px;">All settings have been updated</div>
        <button onclick="closeSavePopup()" style="
            padding:10px 32px; border-radius:10px; cursor:pointer;
            background:rgba(34,197,94,0.12); border:1.5px solid rgba(34,197,94,0.3);
            color:#22c55e; font-family:Rajdhani,sans-serif; font-size:14px;
            font-weight:700; letter-spacing:2px; transition:all .2s;
        " onmouseover="this.style.background='rgba(34,197,94,0.22)'" onmouseout="this.style.background='rgba(34,197,94,0.12)'">
            CLOSE
        </button>
    </div>
</div>
<script>
function closeSavePopup() {
    const p = document.getElementById('save-popup');
    p.style.animation = 'sOverlayOut .25s ease forwards';
    setTimeout(() => p?.remove(), 240);
}
setTimeout(closeSavePopup, 3500);
</script>
@endif

<form method="POST" action="{{ route('dashboard.settings.update') }}" id="settings-form">
@csrf @method('PUT')

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; align-items:start;">

{{-- ════════════════════════ LEFT COLUMN ════════════════════════════════════ --}}
<div style="display:flex; flex-direction:column; gap:20px;">

    {{-- ── NOTIFICATIONS ────────────────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-header">
            <div style="display:flex; align-items:center; gap:12px;">
                <div class="s-icon-box" style="background:rgba(249,115,22,0.1); border-color:rgba(249,115,22,0.25);">🔔</div>
                <div>
                    <div class="s-card-title">NOTIFICATIONS</div>
                    <div class="s-card-sub">Control which alerts appear in the dashboard bell</div>
                </div>
            </div>
        </div>
        <div class="card-body" style="padding:8px 20px;">

            {{-- Low stock --}}
            <div class="s-row">
                <div class="s-info">
                    <div class="s-label">🟠 Low Stock Alert</div>
                    <div class="s-desc">Notify when a product's stock drops to or below the threshold</div>
                </div>
                <label class="ts">
                    <input type="checkbox" name="notif_low_stock" id="chk-low-stock"
                        {{ son($s,'notif_low_stock') ? 'checked' : '' }}
                        onchange="showSub('sub-threshold', this.checked)">
                    <span class="ts-track"></span>
                </label>
            </div>
            <div id="sub-threshold" class="s-sub {{ son($s,'notif_low_stock') ? '' : 's-hidden' }}">
                <div class="s-sub-label">ALERT WHEN STOCK IS AT OR BELOW</div>
                <div style="display:flex; align-items:center; gap:12px; margin-top:8px;">
                    <input type="number" name="notif_low_stock_threshold"
                           value="{{ sval($s,'notif_low_stock_threshold','5') }}"
                           min="1" max="999" class="s-num-input">
                    <span style="color:rgba(255,255,255,0.35); font-size:13px;">units</span>
                    @if($counts['low_stock'] > 0)
                    <span style="font-size:12px; color:#F97316; background:rgba(249,115,22,0.1);
                        border:1px solid rgba(249,115,22,0.2); border-radius:20px; padding:2px 10px;">
                        {{ $counts['low_stock'] }} products affected now
                    </span>
                    @endif
                </div>
            </div>
            <div class="s-divider"></div>

            {{-- New order --}}
            <div class="s-row">
                <div class="s-info">
                    <div class="s-label">📦 New Order Alert</div>
                    <div class="s-desc">Bell notification when a new order is placed today</div>
                </div>
                <label class="ts">
                    <input type="checkbox" name="notif_new_order" {{ son($s,'notif_new_order') ? 'checked' : '' }}>
                    <span class="ts-track"></span>
                </label>
            </div>
            <div class="s-divider"></div>

            {{-- Pending KHQR --}}
            <div class="s-row">
                <div class="s-info">
                    <div class="s-label">📱 KHQR Awaiting Payment</div>
                    <div class="s-desc">Alert when a BAKONG order is still waiting for QR scan</div>
                </div>
                <label class="ts">
                    <input type="checkbox" name="notif_pending_payment" {{ son($s,'notif_pending_payment') ? 'checked' : '' }}>
                    <span class="ts-track"></span>
                </label>
            </div>
            <div class="s-divider"></div>

            {{-- QR confirmed --}}
            <div class="s-row">
                <div class="s-info">
                    <div class="s-label">✅ KHQR Auto-Confirmed</div>
                    <div class="s-desc">Alert when ABA BAKONG auto-confirms a customer's QR payment</div>
                </div>
                <label class="ts">
                    <input type="checkbox" name="notif_qr_confirmed" {{ son($s,'notif_qr_confirmed') ? 'checked' : '' }}>
                    <span class="ts-track"></span>
                </label>
            </div>
            <div class="s-divider"></div>

            {{-- Delivery confirmed --}}
            <div class="s-row">
                <div class="s-info">
                    <div class="s-label">🚚 Delivery Confirmed</div>
                    <div class="s-desc">Alert when an order delivery is confirmed today</div>
                </div>
                <label class="ts">
                    <input type="checkbox" name="notif_delivery_confirm" {{ son($s,'notif_delivery_confirm') ? 'checked' : '' }}>
                    <span class="ts-track"></span>
                </label>
            </div>

        </div>
    </div>

    {{-- ── STORE ────────────────────────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-header">
            <div style="display:flex; align-items:center; gap:12px;">
                <div class="s-icon-box" style="background:rgba(59,130,246,0.1); border-color:rgba(59,130,246,0.25);">🏪</div>
                <div>
                    <div class="s-card-title">STORE</div>
                    <div class="s-card-sub">Store identity and open/close status</div>
                </div>
            </div>
        </div>
        <div class="card-body" style="padding:8px 20px;">

            {{-- Store open toggle --}}
            <div class="s-row">
                <div class="s-info">
                    <div class="s-label">🟢 Store Open</div>
                    <div class="s-desc">When OFF, the storefront shows a "We're closed" page to customers</div>
                </div>
                <label class="ts">
                    <input type="checkbox" name="store_open" {{ son($s,'store_open') ? 'checked' : '' }}>
                    <span class="ts-track ts-green"></span>
                </label>
            </div>
            <div class="s-divider"></div>

            <div style="padding:12px 0 4px;">
                <div class="s-sub-label">STORE DISPLAY NAME</div>
                <input type="text" name="store_name"
                       value="{{ sval($s,'store_name','Tronmatix Computer') }}"
                       class="s-input" style="margin-top:8px;" placeholder="e.g. Tronmatix Computer" />
            </div>

            <div style="padding:12px 0 4px;">
                <div class="s-sub-label">DEFAULT CURRENCY</div>
                <select name="store_currency" class="s-input" style="margin-top:8px; cursor:pointer;">
                    @foreach(['USD'=>'🇺🇸 USD — US Dollar','KHR'=>'🇰🇭 KHR — Khmer Riel','EUR'=>'🇪🇺 EUR — Euro','SGD'=>'🇸🇬 SGD — Singapore Dollar'] as $code => $label)
                    <option value="{{ $code }}" {{ sval($s,'store_currency','USD')===$code?'selected':'' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>

</div>

{{-- ════════════════════════ RIGHT COLUMN ═══════════════════════════════════ --}}
<div style="display:flex; flex-direction:column; gap:20px;">

    {{-- ── LIVE ALERTS PANEL ────────────────────────────────────────────────── --}}
    <div class="card" style="border-color:rgba(249,115,22,0.18);">
        <div class="card-header" style="border-color:rgba(249,115,22,0.12);">
            <div style="display:flex; align-items:center; gap:12px;">
                <div class="s-icon-box" style="background:rgba(249,115,22,0.1); border-color:rgba(249,115,22,0.25);">📊</div>
                <div>
                    <div class="s-card-title" style="color:#F97316;">LIVE ALERTS RIGHT NOW</div>
                    <div class="s-card-sub">Click any card to jump to the relevant page</div>
                </div>
            </div>
            <a href="{{ route('dashboard.settings') }}" style="
                font-size:12px; color:rgba(255,255,255,0.3); text-decoration:none;
                background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08);
                border-radius:6px; padding:4px 10px; letter-spacing:1px;
                transition:color .2s;" onmouseover="this.style.color='#F97316'" onmouseout="this.style.color='rgba(255,255,255,0.3)'">
                ↺ REFRESH
            </a>
        </div>
        <div class="card-body" style="padding:16px 20px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">

                @php $alertGrid = [
                    ['Low Stock',         $counts['low_stock'],       '🟠','#F97316','rgba(249,115,22,.12)','rgba(249,115,22,.25)', route('dashboard.products')],
                    ['New Orders Today',  $counts['pending_orders'],  '📦','#eab308','rgba(234,179,8,.12)', 'rgba(234,179,8,.25)',  route('dashboard.orders',['status'=>'pending'])],
                    ['Awaiting KHQR',     $counts['pending_payment'], '📱','#3b82f6','rgba(59,130,246,.12)','rgba(59,130,246,.25)', route('dashboard.orders')],
                    ['KHQR Paid Today',   $counts['qr_confirmed'],    '✅','#22c55e','rgba(34,197,94,.12)', 'rgba(34,197,94,.25)',  route('dashboard.orders')],
                    ['Delivered Today',   $counts['delivered_today'], '🚚','#a78bfa','rgba(167,139,250,.12)','rgba(167,139,250,.25)',route('dashboard.orders',['status'=>'delivered'])],
                ]; @endphp

                @foreach($alertGrid as [$label,$count,$icon,$color,$bg,$border,$url])
                <a href="{{ $url }}" style="
                    display:block; text-decoration:none; padding:14px 16px;
                    background:{{ $bg }}; border:1.5px solid {{ $count > 0 ? $border : 'rgba(255,255,255,0.06)' }};
                    border-radius:14px; transition:transform .15s, box-shadow .15s;
                    {{ $count > 0 ? 'box-shadow:0 0 16px '.$bg.';' : '' }}
                " onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 24px rgba(0,0,0,0.3)'"
                   onmouseout="this.style.transform='none'; this.style.boxShadow='{{ $count>0 ? '0 0 16px '.$bg : 'none' }}'">
                    <div style="font-size:20px; margin-bottom:4px;">{{ $icon }}</div>
                    <div style="font-size:30px; font-weight:900; color:{{ $count>0?$color:'rgba(255,255,255,0.2)' }};
                         line-height:1; font-family:Rajdhani,sans-serif;">{{ $count }}</div>
                    <div style="font-size:11px; color:rgba(255,255,255,0.35); margin-top:5px; letter-spacing:0.5px;">{{ $label }}</div>
                </a>
                @endforeach

                {{-- Extra "all good" card if everything is 0 --}}
                @if(array_sum(array_column($alertGrid, 1)) === 0)
                <div style="grid-column:1/-1; text-align:center; padding:20px;
                    background:rgba(34,197,94,0.05); border:1px solid rgba(34,197,94,0.15); border-radius:14px;">
                    <div style="font-size:28px; margin-bottom:6px;">🎉</div>
                    <div style="color:#22c55e; font-weight:700; font-size:14px; letter-spacing:1px;">All Clear!</div>
                    <div style="color:rgba(255,255,255,0.3); font-size:12px; margin-top:4px;">No alerts at the moment</div>
                </div>
                @endif

            </div>
            <div style="margin-top:10px; font-size:11px; color:rgba(255,255,255,0.2); text-align:right;">
                As of {{ now()->format('d M Y, H:i:s') }}
            </div>
        </div>
    </div>

    {{-- ── ORDER AUTOMATION ─────────────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-header">
            <div style="display:flex; align-items:center; gap:12px;">
                <div class="s-icon-box" style="background:rgba(167,139,250,0.1); border-color:rgba(167,139,250,0.25);">⚙️</div>
                <div>
                    <div class="s-card-title">ORDER AUTOMATION</div>
                    <div class="s-card-sub">Rules that run automatically on orders</div>
                </div>
            </div>
        </div>
        <div class="card-body" style="padding:8px 20px;">

            {{-- Auto-confirm cash --}}
            <div class="s-row">
                <div class="s-info">
                    <div class="s-label">💵 Auto-confirm Cash Orders</div>
                    <div class="s-desc">Automatically move COD orders from <em>Pending</em> → <em>Confirmed</em> immediately after placement</div>
                </div>
                <label class="ts">
                    <input type="checkbox" name="order_auto_confirm_cash" {{ son($s,'order_auto_confirm_cash') ? 'checked' : '' }}>
                    <span class="ts-track"></span>
                </label>
            </div>
            <div class="s-divider"></div>

            {{-- Auto-cancel stale orders --}}
            <div class="s-row" style="align-items:flex-start; padding-top:16px; padding-bottom:16px;">
                <div class="s-info">
                    <div class="s-label">🗑️ Auto-cancel Stale Orders</div>
                    <div class="s-desc">Cancel <em>Pending</em> orders that haven't been touched after N hours.<br>Set to <strong style="color:#fff;">0</strong> to disable.</div>
                </div>
                <div style="display:flex; align-items:center; gap:8px; flex-shrink:0; padding-top:4px;">
                    <input type="number" name="order_auto_cancel_hours"
                           value="{{ sval($s,'order_auto_cancel_hours','0') }}"
                           min="0" max="720" class="s-num-input">
                    <span style="color:rgba(255,255,255,0.3); font-size:12px; white-space:nowrap;">hrs</span>
                </div>
            </div>

        </div>
    </div>

    {{-- ── DISPLAY ──────────────────────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-header">
            <div style="display:flex; align-items:center; gap:12px;">
                <div class="s-icon-box" style="background:rgba(34,197,94,0.1); border-color:rgba(34,197,94,0.25);">🖥️</div>
                <div>
                    <div class="s-card-title">DISPLAY</div>
                    <div class="s-card-sub">Pagination size for orders and products</div>
                </div>
            </div>
        </div>
        <div class="card-body" style="padding:16px 20px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div>
                    <div class="s-sub-label">ORDERS PER PAGE</div>
                    <input type="number" name="dashboard_rows_per_page"
                           value="{{ sval($s,'dashboard_rows_per_page','20') }}"
                           min="5" max="200" class="s-input" style="margin-top:8px; text-align:center;">
                </div>
                <div>
                    <div class="s-sub-label">PRODUCTS PER PAGE</div>
                    <input type="number" name="products_per_page"
                           value="{{ sval($s,'products_per_page','12') }}"
                           min="4" max="100" class="s-input" style="margin-top:8px; text-align:center;">
                </div>
            </div>
        </div>
    </div>

    {{-- ── SAVE / RESET buttons ─────────────────────────────────────────────── --}}
    <div style="display:flex; gap:12px;">
        <button type="submit" id="save-btn" style="
            flex:2; padding:15px; border-radius:12px; border:none; cursor:pointer;
            background:linear-gradient(135deg,#F97316,#ea580c); color:#fff;
            font-family:Rajdhani,sans-serif; font-size:16px; font-weight:800;
            letter-spacing:2px; box-shadow:0 4px 20px rgba(249,115,22,0.35);
            transition:all .2s; display:flex; align-items:center; justify-content:center; gap:8px;
        " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 28px rgba(249,115,22,0.5)'"
           onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 20px rgba(249,115,22,0.35)'">
            <span id="save-icon">💾</span>
            <span id="save-text">SAVE SETTINGS</span>
        </button>
        <a href="{{ route('dashboard.settings.reset') }}"
           onclick="return confirm('Reset all settings to factory defaults?')"
           style="
            flex:1; padding:15px; border-radius:12px; text-align:center; text-decoration:none;
            border:1.5px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04);
            color:rgba(255,255,255,0.4); font-family:Rajdhani,sans-serif;
            font-size:14px; font-weight:700; letter-spacing:1px;
            display:flex; align-items:center; justify-content:center; gap:6px;
            transition:all .2s;"
           onmouseover="this.style.borderColor='rgba(239,68,68,0.4)'; this.style.color='#ef4444'; this.style.background='rgba(239,68,68,0.06)'"
           onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.color='rgba(255,255,255,0.4)'; this.style.background='rgba(255,255,255,0.04)'">
            🔄 RESET
        </a>
    </div>

</div>
</div>{{-- end grid --}}
</form>

{{-- ════════════════════════════════════════════════════════════════════════════
     ROLE PERMISSIONS MATRIX — only admin/superadmin can save changes
════════════════════════════════════════════════════════════════════════════ --}}
@php
    $currentAdminRole = Auth::guard('admin')->user()->role ?? 'viewer';
    $canEditPerms     = in_array($currentAdminRole, ['admin','superadmin']);

    // Permission matrix definition: [feature_key => display_label]
    $permFeatures = [
        'dashboard'  => ['label' => 'Dashboard',         'icon' => '📊'],
        'products'   => ['label' => 'Products & Banners', 'icon' => '📦'],
        'orders'     => ['label' => 'Orders (view)',      'icon' => '📋'],
        'orders_edit'=> ['label' => 'Orders (edit status)','icon'=> '✏️'],
        'users'      => ['label' => 'Users Management',   'icon' => '👥'],
        'discounts'  => ['label' => 'Discounts',          'icon' => '🏷️'],
        'settings'   => ['label' => 'Settings',           'icon' => '⚙️'],
        'staff'      => ['label' => 'Staff & Roles',      'icon' => '🛡️'],
    ];

    // Roles that can be configured (superadmin is always full — not editable)
    $permRoles = [
        'admin'  => ['label' => 'Admin',   'color' => '#F97316', 'icon' => '🛡️'],
        'editor' => ['label' => 'Editor',  'color' => '#3b82f6', 'icon' => '✏️'],
        'seller' => ['label' => 'Seller',  'color' => '#10b981', 'icon' => '🏪'],
        'viewer' => ['label' => 'Viewer',  'color' => '#a78bfa', 'icon' => '👁️'],
    ];

    // Default locked values for superadmin (always all true)
    $superadminPerms = array_fill_keys(array_keys($permFeatures), true);

    // Helper to read saved perm from settings: perm_{role}_{feature}
    $perm = function($role, $feature) use ($s) {
        $key = "perm_{$role}_{$feature}";
        // Sensible defaults if not saved yet
        $defaults = [
            'admin_dashboard'   => '1','admin_products' => '1','admin_orders'  => '1',
            'admin_orders_edit' => '1','admin_users'    => '1','admin_discounts'=> '1',
            'admin_settings'    => '1','admin_staff'    => '1',
            'editor_dashboard'  => '1','editor_products'=> '1','editor_orders' => '1',
            'editor_orders_edit'=> '0','editor_users'   => '0','editor_discounts'=>'1',
            'editor_settings'   => '0','editor_staff'   => '0',
            'seller_dashboard'  => '1','seller_products'=> '1','seller_orders' => '1',
            'seller_orders_edit'=> '1','seller_users'   => '0','seller_discounts'=>'1',
            'seller_settings'   => '0','seller_staff'   => '0',
            'viewer_dashboard'  => '1','viewer_products'=> '0','viewer_orders'  => '1',
            'viewer_orders_edit'=> '0','viewer_users'   => '0','viewer_discounts'=>'0',
            'viewer_settings'   => '0','viewer_staff'   => '0',
        ];
        return ($s[$key] ?? $defaults["{$role}_{$feature}"] ?? '0') === '1';
    };
@endphp

<div style="margin-top:24px;">

    {{-- Section header --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:42px; height:42px; border-radius:12px; background:rgba(167,139,250,0.12);
                        border:1px solid rgba(167,139,250,0.3); display:flex; align-items:center; justify-content:center; font-size:20px;">🔐</div>
            <div>
                <div style="font-size:17px; font-weight:800; letter-spacing:2px;">ROLE PERMISSIONS</div>
                <div style="font-size:12px; color:rgba(255,255,255,0.35); margin-top:2px;">
                    Define what each role can access across the dashboard
                </div>
            </div>
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
            <a href="{{ route('dashboard.staff') }}"
               style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px;
                      border-radius:8px; border:1px solid rgba(167,139,250,0.3); background:rgba(167,139,250,0.08);
                      color:#a78bfa; font-size:13px; font-weight:700; letter-spacing:1px; text-decoration:none;
                      transition:all .2s;"
               onmouseover="this.style.background='rgba(167,139,250,0.16)'"
               onmouseout="this.style.background='rgba(167,139,250,0.08)'">
                👥 MANAGE STAFF
            </a>
            @if(!$canEditPerms)
            <div style="display:flex; align-items:center; gap:6px; padding:8px 14px; border-radius:8px;
                        background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2);">
                <span style="font-size:13px;">🔒</span>
                <span style="font-size:12px; color:#ef4444; font-weight:700; letter-spacing:1px;">ADMIN ONLY</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Permission matrix card --}}
    <div class="card" style="{{ !$canEditPerms ? 'opacity:0.75; pointer-events:none;' : '' }}">
        <form method="POST" action="{{ route('dashboard.settings.permissions') }}" id="perms-form">
            @csrf @method('PUT')

            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.07);">
                            <th style="padding:16px 20px; text-align:left; font-size:11px; letter-spacing:2px;
                                       color:rgba(255,255,255,0.35); white-space:nowrap; font-weight:700; width:220px;">
                                FEATURE / MODULE
                            </th>

                            {{-- Superadmin: always full --}}
                            <th style="padding:16px 14px; text-align:center; white-space:nowrap;">
                                <div style="display:inline-flex; flex-direction:column; align-items:center; gap:4px;">
                                    <div style="width:36px; height:36px; border-radius:10px;
                                                background:rgba(249,115,22,0.15); border:1px solid rgba(249,115,22,0.3);
                                                display:flex; align-items:center; justify-content:center; font-size:18px;">👑</div>
                                    <span style="font-size:10px; letter-spacing:1.5px; color:#F97316; font-weight:800;">SUPER</span>
                                </div>
                            </th>

                            @foreach($permRoles as $roleKey => $roleMeta)
                            <th style="padding:16px 14px; text-align:center; white-space:nowrap;">
                                <div style="display:inline-flex; flex-direction:column; align-items:center; gap:4px;">
                                    <div style="width:36px; height:36px; border-radius:10px;
                                                background:{{ $roleMeta['color'] }}18; border:1px solid {{ $roleMeta['color'] }}44;
                                                display:flex; align-items:center; justify-content:center; font-size:18px;">
                                        {{ $roleMeta['icon'] }}
                                    </div>
                                    <span style="font-size:10px; letter-spacing:1.5px; color:{{ $roleMeta['color'] }}; font-weight:800;">
                                        {{ strtoupper($roleMeta['label']) }}
                                    </span>
                                </div>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permFeatures as $featureKey => $featureMeta)
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                            <td style="padding:14px 20px;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <span style="font-size:18px;">{{ $featureMeta['icon'] }}</span>
                                    <span style="font-size:14px; font-weight:600; color:rgba(255,255,255,0.85);">
                                        {{ $featureMeta['label'] }}
                                    </span>
                                </div>
                            </td>

                            {{-- Superadmin always ✅ --}}
                            <td style="padding:14px; text-align:center;">
                                <span style="display:inline-flex; align-items:center; justify-content:center;
                                             width:28px; height:28px; border-radius:8px;
                                             background:rgba(249,115,22,0.12); border:1px solid rgba(249,115,22,0.3);">
                                    <svg width="14" height="14" fill="none" stroke="#F97316" stroke-width="2.5" viewBox="0 0 24 24">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                </span>
                            </td>

                            @foreach($permRoles as $roleKey => $roleMeta)
                            @php $checked = $perm($roleKey, $featureKey); @endphp
                            <td style="padding:14px; text-align:center;">
                                {{-- Staff & Settings: admin always ON, cannot be turned off --}}
                                @if($roleKey === 'admin' && in_array($featureKey, ['settings','staff','orders_edit','users']))
                                    <input type="hidden" name="perm_{{ $roleKey }}_{{ $featureKey }}" value="1">
                                    <span style="display:inline-flex; align-items:center; justify-content:center;
                                                 width:28px; height:28px; border-radius:8px;
                                                 background:rgba(249,115,22,0.12); border:1px solid rgba(249,115,22,0.3);"
                                          title="Admin always has this permission">
                                        <svg width="14" height="14" fill="none" stroke="#F97316" stroke-width="2.5" viewBox="0 0 24 24">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                    </span>
                                @else
                                    <label class="perm-toggle" style="cursor:{{ $canEditPerms ? 'pointer' : 'not-allowed' }};">
                                        <input type="checkbox"
                                               name="perm_{{ $roleKey }}_{{ $featureKey }}"
                                               value="1"
                                               {{ $checked ? 'checked' : '' }}
                                               {{ !$canEditPerms ? 'disabled' : '' }}
                                               onchange="markPermDirty()"
                                               style="display:none;" />
                                        <span class="perm-check {{ $checked ? 'perm-on' : 'perm-off' }}"
                                              data-color="{{ $roleMeta['color'] }}"></span>
                                    </label>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Save bar --}}
            @if($canEditPerms)
            <div id="perm-save-bar" style="
                padding:16px 20px; border-top:1px solid rgba(255,255,255,0.07);
                display:flex; align-items:center; justify-content:space-between; gap:12px;
                background:rgba(167,139,250,0.04);
                opacity:0; pointer-events:none; transition:opacity .25s;
            ">
                <span style="font-size:13px; color:rgba(255,255,255,0.4);">
                    ⚠️ You have unsaved permission changes
                </span>
                <div style="display:flex; gap:10px;">
                    <button type="button" onclick="resetPermissions()"
                            style="padding:9px 18px; border-radius:8px; border:1px solid rgba(255,255,255,0.12);
                                   background:transparent; color:rgba(255,255,255,0.5);
                                   font-family:Rajdhani,sans-serif; font-size:13px; font-weight:700;
                                   letter-spacing:1px; cursor:pointer; transition:all .2s;"
                            onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">
                        DISCARD
                    </button>
                    <button type="submit" id="perm-save-btn"
                            style="display:flex; align-items:center; gap:6px; padding:9px 20px;
                                   border-radius:8px; border:none;
                                   background:linear-gradient(135deg,#a78bfa,#7c3aed); color:#fff;
                                   font-family:Rajdhani,sans-serif; font-size:13px; font-weight:800;
                                   letter-spacing:1px; cursor:pointer; transition:all .2s;
                                   box-shadow:0 4px 16px rgba(167,139,250,0.3);">
                        🔐 SAVE PERMISSIONS
                    </button>
                </div>
            </div>
            @else
            <div style="padding:14px 20px; border-top:1px solid rgba(255,255,255,0.07);
                        display:flex; align-items:center; gap:8px;">
                <span style="font-size:13px; color:rgba(239,68,68,0.7);">🔒</span>
                <span style="font-size:13px; color:rgba(255,255,255,0.3);">
                    You need <strong style="color:#F97316;">Admin</strong> or
                    <strong style="color:#F97316;">Super Admin</strong> role to modify permissions.
                </span>
            </div>
            @endif
        </form>
    </div>

    {{-- Role legend --}}
    <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:14px;">
        @php
            $legend = [
                ['👑','SUPER ADMIN','#F97316','Full owner-level access to everything'],
                ['🛡️','ADMIN',     '#F97316','Full access; cannot demote superadmin'],
                ['✏️','EDITOR',    '#3b82f6','Products, banners & discounts; read-only orders'],
                ['🏪','SELLER',    '#10b981','Products, orders & discounts management'],
                ['👁️','VIEWER',    '#a78bfa','Dashboard & orders read-only only'],
            ];
        @endphp
        @foreach($legend as [$icon,$label,$color,$desc])
        <div style="display:flex; align-items:center; gap:8px; padding:10px 14px; border-radius:10px;
                    background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.06); flex:1; min-width:200px;">
            <span style="font-size:18px;">{{ $icon }}</span>
            <div>
                <div style="font-size:12px; font-weight:800; letter-spacing:1px; color:{{ $color }};">{{ $label }}</div>
                <div style="font-size:11px; color:rgba(255,255,255,0.3); margin-top:2px;">{{ $desc }}</div>
            </div>
        </div>
        @endforeach
    </div>

</div>

{{-- ════════════════════════ STYLES ══════════════════════════════════════════ --}}
<style>
/* Popup animations */
@keyframes sOverlayIn  { from{opacity:0}    to{opacity:1} }
@keyframes sOverlayOut { from{opacity:1}    to{opacity:0} }
@keyframes sBoxIn      { from{opacity:0;transform:scale(.88) translateY(20px)} to{opacity:1;transform:scale(1) translateY(0)} }
@keyframes sPopIn      { 0%{transform:scale(0) rotate(-12deg)} 60%{transform:scale(1.2) rotate(4deg)} 100%{transform:scale(1) rotate(0)} }
@keyframes sSubSlide   { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:translateY(0)} }

/* Icon box */
.s-icon-box {
    width:38px; height:38px; border-radius:10px; flex-shrink:0;
    border:1px solid; display:flex; align-items:center; justify-content:center; font-size:18px;
}

/* Card titles */
.s-card-title { font-size:15px; font-weight:800; letter-spacing:1.5px; padding:0; }
.s-card-sub   { font-size:12px; color:rgba(255,255,255,0.3); margin-top:2px; }

/* Setting row */
.s-row {
    display:flex; align-items:center; justify-content:space-between;
    gap:16px; padding:14px 0;
}
.s-info { flex:1; min-width:0; }
.s-label { font-size:14px; font-weight:700; color:rgba(255,255,255,0.88); margin-bottom:3px; }
.s-desc  { font-size:12px; color:rgba(255,255,255,0.3); line-height:1.5; }
.s-desc em     { color:rgba(249,115,22,0.7); font-style:normal; }
.s-desc strong { color:rgba(255,255,255,0.7); }
.s-divider { height:1px; background:rgba(255,255,255,0.055); }

/* Sub option */
.s-sub {
    margin:4px 0 10px; padding:12px 14px;
    background:rgba(249,115,22,0.04); border:1px solid rgba(249,115,22,0.12);
    border-radius:10px; animation:sSubSlide .2s ease;
}
.s-hidden { display:none; }
.s-sub-label { font-size:11px; letter-spacing:2px; color:rgba(255,255,255,0.3); font-weight:700; }

/* Toggle switch */
.ts { position:relative; width:52px; height:28px; flex-shrink:0; cursor:pointer; }
.ts input { opacity:0; width:0; height:0; position:absolute; }
.ts-track {
    position:absolute; inset:0; border-radius:28px;
    background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.1);
    transition:background .3s, border-color .3s, box-shadow .3s; cursor:pointer;
}
.ts-track::before {
    content:''; position:absolute; width:20px; height:20px; left:3px; top:3px;
    border-radius:50%; background:#fff;
    transition:transform .3s cubic-bezier(0.34,1.56,0.64,1);
    box-shadow:0 2px 8px rgba(0,0,0,0.3);
}
.ts input:checked + .ts-track {
    background:#F97316; border-color:#F97316;
    box-shadow:0 0 14px rgba(249,115,22,0.45);
}
.ts input:checked + .ts-track::before { transform:translateX(24px); }
/* Green variant */
.ts input:checked + .ts-track.ts-green {
    background:#22c55e; border-color:#22c55e;
    box-shadow:0 0 14px rgba(34,197,94,0.45);
}

/* Inputs */
.s-input {
    width:100%; background:rgba(255,255,255,0.07);
    border:1.5px solid rgba(255,255,255,0.1); color:#fff;
    border-radius:10px; padding:10px 14px;
    font-family:Rajdhani,sans-serif; font-size:15px; font-weight:600;
    outline:none; transition:border-color .2s;
}
.s-input:focus { border-color:#F97316; }
.s-input option { background:#1a1a1a; }
.s-num-input {
    width:72px; background:rgba(255,255,255,0.07);
    border:1.5px solid rgba(255,255,255,0.1); color:#fff;
    border-radius:8px; padding:7px 10px;
    font-family:Rajdhani,sans-serif; font-size:16px; font-weight:700;
    outline:none; text-align:center; transition:border-color .2s;
}
.s-num-input:focus { border-color:#F97316; }
</style>

{{-- ════════════════════════ SCRIPTS ═════════════════════════════════════════ --}}
<script>
function showSub(id, show) {
    const el = document.getElementById(id);
    if (!el) return;
    if (show) {
        el.classList.remove('s-hidden');
        el.style.animation = 'none';
        el.offsetHeight;
        el.style.animation = 'sSubSlide .2s ease';
    } else {
        el.classList.add('s-hidden');
    }
}

// Save button loading state
document.getElementById('settings-form').addEventListener('submit', function() {
    const btn  = document.getElementById('save-btn');
    const icon = document.getElementById('save-icon');
    const text = document.getElementById('save-text');
    btn.disabled = true;
    btn.style.opacity = '0.85';
    icon.textContent = '⏳';
    text.textContent = 'SAVING...';
});

/* ── Permission toggles ─────────────────────────────────────────────────── */
let permDirty = false;

// Wire up visual toggle behaviour
document.querySelectorAll('.perm-toggle input[type=checkbox]').forEach(function(cb) {
    cb.addEventListener('change', function() {
        const span = this.closest('.perm-toggle').querySelector('.perm-check');
        const color = span.dataset.color || '#a78bfa';
        if (this.checked) {
            span.classList.remove('perm-off');
            span.classList.add('perm-on');
            span.style.setProperty('--perm-color', color);
        } else {
            span.classList.remove('perm-on');
            span.classList.add('perm-off');
        }
    });
});

function markPermDirty() {
    if (permDirty) return;
    permDirty = true;
    const bar = document.getElementById('perm-save-bar');
    if (bar) { bar.style.opacity = '1'; bar.style.pointerEvents = 'auto'; }
}

function resetPermissions() {
    document.querySelectorAll('.perm-toggle input[type=checkbox]').forEach(function(cb) {
        const orig = cb.defaultChecked;
        cb.checked = orig;
        const span = cb.closest('.perm-toggle').querySelector('.perm-check');
        const color = span.dataset.color || '#a78bfa';
        span.classList.toggle('perm-on', orig);
        span.classList.toggle('perm-off', !orig);
    });
    permDirty = false;
    const bar = document.getElementById('perm-save-bar');
    if (bar) { bar.style.opacity = '0'; bar.style.pointerEvents = 'none'; }
}

// Loading state for permissions save
const permsForm = document.getElementById('perms-form');
if (permsForm) {
    permsForm.addEventListener('submit', function() {
        const btn = document.getElementById('perm-save-btn');
        if (btn) { btn.textContent = '⏳ SAVING...'; btn.disabled = true; }
    });
}
</script>

<style>
/* ── Permission toggle checkboxes ───────────────────────────────────────── */
.perm-check {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    transition: background .18s, border-color .18s, transform .15s;
    --perm-color: #a78bfa;
}
.perm-check.perm-on {
    background: rgba(var(--perm-rgb, 167,139,250), 0.15);
    border: 1.5px solid var(--perm-color, #a78bfa);
    color: var(--perm-color, #a78bfa);
}
.perm-check.perm-on::after {
    content: '';
    display: block;
    width: 10px;
    height: 6px;
    border-left: 2.5px solid var(--perm-color, #a78bfa);
    border-bottom: 2.5px solid var(--perm-color, #a78bfa);
    transform: rotate(-45deg) translateY(-1px);
}
.perm-check.perm-off {
    background: rgba(255,255,255,0.03);
    border: 1.5px solid rgba(255,255,255,0.1);
}
.perm-toggle:hover .perm-check.perm-off {
    border-color: rgba(255,255,255,0.25);
    background: rgba(255,255,255,0.06);
}
.perm-toggle:hover .perm-check {
    transform: scale(1.1);
}

/* Permission matrix: scroll on small screens */
@media (max-width: 768px) {
    div[style*="overflow-x:auto"] table,
    .table-wrap table { min-width: 520px; }
}

/* Settings cards stack on mobile */
@media (max-width: 700px) {
    div[style*="display:grid"][style*="grid-template-columns:1fr 1fr"],
    div[style*="display:grid"][style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}

/* Role legend wraps nicely on mobile */
@media (max-width: 540px) {
    div[style*="min-width:200px"] {
        min-width: 100% !important;
        flex: 1 1 100% !important;
    }
}

/* Perm matrix role header compact on tablet */
@media (max-width: 860px) {
    .perm-role-header { font-size: 9px !important; }
}
</style>

@endsection
