@extends('dashboard.layout')
@section('title', 'DASHBOARD')

@section('content')

@php
    use App\Models\AdminSetting;
    $_pRole = Auth::guard('admin')->user()?->role ?? 'viewer';
    $_pFeat = 'dashboard';
    $_pKey  = "perm_{$_pRole}_{$_pFeat}";
    $_pDef  = [
        'admin_dashboard'=>'1','admin_products'=>'1','admin_orders'=>'1',
        'admin_orders_edit'=>'1','admin_users'=>'1','admin_discounts'=>'1',
        'admin_settings'=>'1','admin_staff'=>'1',
        'editor_dashboard'=>'1','editor_products'=>'1','editor_orders'=>'1',
        'editor_orders_edit'=>'0','editor_users'=>'0','editor_discounts'=>'1',
        'editor_settings'=>'0','editor_staff'=>'0',
        'viewer_dashboard'=>'1','viewer_products'=>'0','viewer_orders'=>'1',
        'viewer_orders_edit'=>'0','viewer_users'=>'0','viewer_discounts'=>'0',
        'viewer_settings'=>'0','viewer_staff'=>'0',
    ];
    $_pAccess = $_pRole === 'superadmin'
        || (AdminSetting::get($_pKey, $_pDef["{$_pRole}_{$_pFeat}"] ?? '0') === '1');
    $_pRoleMeta = [
        'superadmin'=>['color'=>'#F97316','icon'=>'👑','label'=>'Super Admin'],
        'admin'     =>['color'=>'#F97316','icon'=>'🛡️','label'=>'Admin'],
        'editor'    =>['color'=>'#3b82f6','icon'=>'✏️', 'label'=>'Editor'],
        'viewer'    =>['color'=>'#a78bfa','icon'=>'👁️', 'label'=>'Viewer'],
    ];
    $_pRM = $_pRoleMeta[$_pRole] ?? $_pRoleMeta['viewer'];
    $_pAllFeats = ['dashboard'=>'📊','products'=>'📦','orders'=>'📋',
                   'orders_edit'=>'✏️','users'=>'👥','discounts'=>'🏷️',
                   'settings'=>'⚙️','staff'=>'🛡️'];
@endphp

@if(!$_pAccess)
{{-- ══════════════════ ACCESS DENIED ══════════════════════════════════════ --}}
<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;
     min-height:60vh;text-align:center;padding:40px 20px;font-family:Rajdhani,sans-serif;
     animation:fadeUp .45s ease both;">
    <div style="width:96px;height:96px;border-radius:28px;margin-bottom:28px;
         background:rgba(239,68,68,0.08);border:1.5px solid rgba(239,68,68,0.25);
         display:flex;align-items:center;justify-content:center;font-size:46px;
         box-shadow:0 0 60px rgba(239,68,68,0.12);animation:lockPulse 2.5s ease-in-out infinite;">🔒</div>
    <div style="font-size:30px;font-weight:900;letter-spacing:3px;color:#ef4444;margin-bottom:8px;">ACCESS DENIED</div>
    <div style="font-size:14px;color:rgba(255,255,255,0.35);margin-bottom:32px;max-width:380px;line-height:1.6;">
        Your role does not have permission to access this module.<br>
        Contact a <span style="color:#F97316;font-weight:700;">Super Admin</span> to request access.
    </div>
    <div style="display:inline-flex;align-items:center;gap:10px;padding:12px 24px;border-radius:16px;
         margin-bottom:32px;background:{{ $_pRM['color'] }}12;border:1.5px solid {{ $_pRM['color'] }}40;">
        <span style="font-size:22px;">{{ $_pRM['icon'] }}</span>
        <div style="text-align:left;">
            <div style="font-size:10px;color:rgba(255,255,255,0.4);letter-spacing:2px;font-weight:700;">YOUR ROLE</div>
            <div style="font-size:16px;font-weight:800;color:{{ $_pRM['color'] }};letter-spacing:1px;">{{ strtoupper($_pRM['label']) }}</div>
        </div>
        <div style="width:1px;height:32px;background:rgba(255,255,255,0.1);margin:0 4px;"></div>
        <div style="text-align:left;">
            <div style="font-size:10px;color:rgba(255,255,255,0.4);letter-spacing:2px;font-weight:700;">MODULE</div>
            <div style="font-size:16px;font-weight:800;color:rgba(255,255,255,0.6);letter-spacing:1px;">{{ strtoupper(str_replace('_',' ','dashboard')) }}</div>
        </div>
    </div>
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);
         border-radius:16px;padding:20px 24px;margin-bottom:32px;max-width:480px;width:100%;">
        <div style="font-size:11px;color:rgba(255,255,255,0.3);letter-spacing:2px;font-weight:700;margin-bottom:16px;text-align:left;">YOUR ACCESS OVERVIEW</div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;">
            @foreach($_pAllFeats as $_fKey => $_fIcon)
            @php
                $_fPKey = "perm_{$_pRole}_{$_fKey}";
                $_fHas  = $_pRole === 'superadmin' || (AdminSetting::get($_fPKey, $_pDef["{$_pRole}_{$_fKey}"] ?? '0') === '1');
                $_fActive = ($_fKey === 'dashboard');
            @endphp
            <div style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:10px 6px;border-radius:10px;
                 background:{{ $_fActive ? 'rgba(239,68,68,0.10)' : ($_fHas ? 'rgba(34,197,94,0.07)' : 'rgba(255,255,255,0.03)') }};
                 border:1px solid {{ $_fActive ? 'rgba(239,68,68,0.3)' : ($_fHas ? 'rgba(34,197,94,0.2)' : 'rgba(255,255,255,0.06)') }};">
                <span style="font-size:18px;{{ !$_fHas ? 'opacity:0.3;' : '' }}">{{ $_fIcon }}</span>
                <span style="font-size:9px;letter-spacing:1px;font-weight:700;
                    color:{{ $_fActive ? '#ef4444' : ($_fHas ? '#22c55e' : 'rgba(255,255,255,0.2)') }};">
                    {{ $_fHas ? '✓' : '✗' }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    <div style="display:flex;gap:12px;flex-wrap:wrap;justify-content:center;">
        <a href="{{ route('dashboard.index') }}" style="display:inline-flex;align-items:center;gap:8px;
           padding:12px 24px;border-radius:12px;text-decoration:none;background:#F97316;color:#fff;
           font-size:14px;font-weight:700;letter-spacing:1px;box-shadow:0 4px 16px rgba(249,115,22,0.3);"
           onmouseover="this.style.background='#fb923c'" onmouseout="this.style.background='#F97316'">
            🏠 GO TO DASHBOARD
        </a>
        <a href="javascript:history.back()" style="display:inline-flex;align-items:center;gap:8px;
           padding:12px 24px;border-radius:12px;text-decoration:none;
           background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);
           color:rgba(255,255,255,0.6);font-size:14px;font-weight:700;letter-spacing:1px;"
           onmouseover="this.style.background='rgba(255,255,255,0.10)'" onmouseout="this.style.background='rgba(255,255,255,0.06)'">
            ← GO BACK
        </a>
    </div>
</div>
<style>
@keyframes fadeUp   { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:none} }
@keyframes lockPulse { 0%,100%{box-shadow:0 0 30px rgba(239,68,68,0.08)} 50%{box-shadow:0 0 60px rgba(239,68,68,0.22)} }
</style>
@else



{{-- ── Stat Cards ────────────────────────────────────────────────────────────── --}}
<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-icon">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
            </svg>
        </div>
        <div>
            <div class="stat-value">{{ number_format($stats['total_users']) }}</div>
            <div class="stat-label">TOTAL USERS</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
            </svg>
        </div>
        <div>
            <div class="stat-value">{{ number_format($stats['total_products']) }}</div>
            <div class="stat-label">TOTAL PRODUCTS</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                <rect x="9" y="3" width="6" height="4" rx="1"/>
            </svg>
        </div>
        <div>
            <div class="stat-value">{{ number_format($stats['total_orders']) }}</div>
            <div class="stat-label">TOTAL ORDERS</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
            </svg>
        </div>
        <div>
            <div class="stat-value">${{ number_format($stats['total_revenue'], 0) }}</div>
            <div class="stat-label">TOTAL REVENUE</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
        </div>
        <div>
            <div class="stat-value">{{ number_format($stats['pending_orders']) }}</div>
            <div class="stat-label">PENDING</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <div>
            <div class="stat-value">{{ number_format($stats['active_orders']) }}</div>
            <div class="stat-label">ACTIVE</div>
        </div>
    </div>

    <div class="stat-card" style="border-color: rgba(168,85,247,0.3);">
        <div class="stat-icon" style="background:rgba(168,85,247,0.1); border-color:rgba(168,85,247,0.25);">
            <svg fill="none" stroke="#A855F7" stroke-width="2" viewBox="0 0 24 24">
                <path d="M7 7h.01M17 17h.01"/>
                <path d="M3 6a3 3 0 013-3h12a3 3 0 013 3v12a3 3 0 01-3 3H6a3 3 0 01-3-3V6z"/>
                <path stroke-linecap="round" d="M7 17L17 7"/>
            </svg>
        </div>
        <div>
            <div class="stat-value" style="color:#A855F7;">${{ number_format($stats['total_discount_used'], 2) }}</div>
            <div class="stat-label">DISCOUNTS SAVED</div>
        </div>
    </div>

    <div class="stat-card" style="border-color: rgba(34,197,94,0.25);">
        <div class="stat-icon" style="background:rgba(34,197,94,0.08); border-color:rgba(34,197,94,0.2);">
            <svg fill="none" stroke="#22C55E" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 14l2 2 4-4"/>
                <path d="M3 6a3 3 0 013-3h12a3 3 0 013 3v12a3 3 0 01-3 3H6a3 3 0 01-3-3V6z"/>
            </svg>
        </div>
        <div>
            <div class="stat-value" style="color:#22C55E;">{{ number_format($stats['active_discounts']) }}</div>
            <div class="stat-label">ACTIVE CODES</div>
        </div>
    </div>

</div>

{{-- ── Export Panel ────────────────────────────────────────────────────────────── --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <span class="card-title">🏷️ EXPORT</span>
        <span class="chart-badge">Last 30 Days</span>
    </div>
    <div class="card-body">
        <div style="display:flex; flex-wrap:wrap; align-items:center; gap:16px;">

            {{-- Quick stats for the month --}}
            <div style="display:flex; gap:12px; flex-wrap:wrap; flex:1; min-width:240px;">
                <div style="background:rgba(168,85,247,0.08); border:1px solid rgba(168,85,247,0.2);
                     border-radius:10px; padding:12px 18px;">
                    <div style="font-size:11px; color:rgba(255,255,255,0.4); letter-spacing:1.5px; margin-bottom:4px;">THIS MONTH SAVED</div>
                    <div style="font-size:22px; font-weight:700; color:#A855F7;">
                        ${{ number_format($stats['monthly_discount_used'], 2) }}
                    </div>
                </div>
                <div style="background:rgba(249,115,22,0.08); border:1px solid rgba(249,115,22,0.2);
                     border-radius:10px; padding:12px 18px;">
                    <div style="font-size:11px; color:rgba(255,255,255,0.4); letter-spacing:1.5px; margin-bottom:4px;">TIMES USED (30 DAYS)</div>
                    <div style="font-size:22px; font-weight:700; color:#F97316;">
                        {{ number_format($stats['monthly_discount_count']) }}
                    </div>
                </div>
            </div>

            {{-- ── Dashboard Full Export Form ───────────────────────────────────────
                 FIX 1: action uses route() helper instead of hardcoded url()
                 FIX 2: removed @csrf — GET requests do not use CSRF tokens
                 FIX 3: name="from" / name="to" send Y-m which the controller converts
            ──────────────────────────────────────────────────────────────────────── --}}
            <form action="{{ route('dashboard.export') }}" method="GET"
                  style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">

                <div style="display:flex; flex-direction:column; gap:4px;">
                    <label style="font-size:11px; color:rgba(255,255,255,0.4); letter-spacing:1.5px;">FROM MONTH</label>
                    <input type="month"
                           name="from"
                           value="{{ now()->subMonth()->format('Y-m') }}"
                           max="{{ now()->format('Y-m') }}"
                           style="background:#1A1A1A; border:1px solid rgba(255,255,255,0.12); color:#fff;
                                  border-radius:8px; padding:8px 12px; font-size:14px;" />
                </div>

                <div style="display:flex; flex-direction:column; gap:4px;">
                    <label style="font-size:11px; color:rgba(255,255,255,0.4); letter-spacing:1.5px;">TO MONTH</label>
                    <input type="month"
                           name="to"
                           value="{{ now()->format('Y-m') }}"
                           max="{{ now()->format('Y-m') }}"
                           style="background:#1A1A1A; border:1px solid rgba(255,255,255,0.12); color:#fff;
                                  border-radius:8px; padding:8px 12px; font-size:14px;" />
                </div>

                <div style="display:flex; flex-direction:column; gap:4px;">
                    <label style="font-size:11px; color:rgba(255,255,255,0.4); letter-spacing:1.5px;">FORMAT</label>
                    <select name="format"
                            style="background:#1A1A1A; border:1px solid rgba(255,255,255,0.12); color:#fff;
                                   border-radius:8px; padding:8px 14px; font-size:14px;">
                        {{-- xlsx exports all 8 sheets; csv exports Summary sheet only --}}
                        <option value="xlsx">Excel (.xlsx) — All 8 sheets</option>
                        <option value="csv">CSV (.csv) — Summary only</option>
                    </select>
                </div>

                <div style="display:flex; flex-direction:column; gap:4px;">
                    <label style="color:transparent; font-size:11px;">.</label>
                    <button type="submit" class="btn btn-orange" style="padding:8px 20px;">
                        ⬇ Export Dashboard
                    </button>
                </div>

            </form>

        </div>

        {{-- Validation error display --}}
        @if($errors->has('export'))
        <div style="margin-top:12px; padding:10px 16px; background:rgba(239,68,68,0.1);
             border:1px solid rgba(239,68,68,0.3); border-radius:8px;
             color:#ef4444; font-size:13px; font-weight:600;">
            ⚠ {{ $errors->first('export') }}
        </div>
        @endif

        {{-- Top codes this month mini-table --}}
        @if(isset($top_discount_codes) && $top_discount_codes->isNotEmpty())
        <div style="margin-top:20px;">
            <div style="font-size:11px; color:rgba(255,255,255,0.3); letter-spacing:2px; font-weight:700;
                 margin-bottom:10px;">TOP CODES THIS MONTH</div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>CODE</th>
                            <th>TYPE</th>
                            <th>VALUE</th>
                            <th>TIMES USED</th>
                            <th>TOTAL SAVED ($)</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($top_discount_codes as $dc)
                        <tr>
                            <td>
                                <span style="font-family:monospace; font-size:13px; font-weight:700;
                                      color:#F97316; background:rgba(249,115,22,0.08);
                                      padding:3px 8px; border-radius:6px;">
                                    {{ $dc->code }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $dc->type === 'percentage' ? 'badge-processing' : 'badge-orange' }}">
                                    {{ strtoupper($dc->type) }}
                                </span>
                            </td>
                            <td style="font-weight:700;">
                                {{ $dc->type === 'percentage' ? $dc->value.'%' : '$'.number_format($dc->value, 2) }}
                            </td>
                            <td style="color:#F97316; font-weight:700;">{{ $dc->monthly_uses }}</td>
                            <td style="color:#A855F7; font-weight:700;">${{ number_format($dc->monthly_saved, 2) }}</td>
                            <td>
                                <span class="badge {{ $dc->status === 'active' ? 'badge-confirmed' : ($dc->status === 'expired' ? 'badge-cancelled' : 'badge-gray') }}">
                                    {{ strtoupper($dc->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ── Row 1: Monthly Revenue + Monthly Orders ──────────────────────────────── --}}
<div class="chart-grid-2" style="margin-bottom:20px;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">📈 MONTHLY REVENUE</span>
            <span class="chart-badge">Last 12 Months</span>
        </div>
        <div class="card-body">
            <canvas id="revenueChart" height="110"></canvas>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <span class="card-title">📦 MONTHLY ORDERS</span>
            <span class="chart-badge">Last 12 Months</span>
        </div>
        <div class="card-body">
            <canvas id="ordersChart" height="110"></canvas>
        </div>
    </div>
</div>

{{-- ── Row 2: Daily Sales + User Registrations ──────────────────────────────── --}}
<div class="chart-grid-2" style="margin-bottom:20px;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">📅 DAILY SALES</span>
            <span class="chart-badge">Last 14 Days</span>
        </div>
        <div class="card-body">
            <canvas id="dailyChart" height="110"></canvas>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <span class="card-title">👤 USER REGISTRATIONS</span>
            <span class="chart-badge">Last 12 Months</span>
        </div>
        <div class="card-body">
            <canvas id="usersChart" height="110"></canvas>
        </div>
    </div>
</div>

{{-- ── Row 3: Order Status Pie + Category Revenue Doughnut ─────────────────── --}}
<div class="chart-grid-2" style="margin-bottom:20px;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">🥧 ORDER STATUS</span>
            <span class="chart-badge">All Time</span>
        </div>
        <div class="card-body" style="display:flex; justify-content:center;">
            <div style="width:260px; height:260px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <span class="card-title">🍩 REVENUE BY CATEGORY</span>
            <span class="chart-badge">All Time</span>
        </div>
        <div class="card-body" style="display:flex; justify-content:center;">
            <div style="width:260px; height:260px;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ── Row 4: Recent Orders + Top Products + Low Stock ─────────────────────── --}}
<div class="chart-grid-2" style="margin-bottom:20px;">

    {{-- Recent Orders --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">🕒 RECENT ORDERS</span>
            <a href="{{ route('dashboard.orders') }}" class="btn btn-outline btn-sm">VIEW ALL</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ORDER ID</th>
                        <th>CUSTOMER</th>
                        <th>TOTAL</th>
                        <th>STATUS</th>
                        <th>DATE</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_orders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('dashboard.orders.show', $order) }}"
                               style="color:#F97316; font-weight:700; font-family:monospace; font-size:12px; text-decoration:none;">
                                {{ $order->order_id }}
                            </a>
                        </td>
                        <td style="font-weight:600;">{{ $order->user?->username ?? 'Guest' }}</td>
                        <td style="color:#F97316; font-weight:700;">${{ number_format($order->total, 2) }}</td>
                        <td><span class="badge badge-{{ $order->status }}">{{ strtoupper($order->status) }}</span></td>
                        <td style="color:rgba(255,255,255,0.4); font-size:12px;">{{ $order->created_at->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center; color:rgba(255,255,255,0.3); padding:30px;">No orders yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Top Products + Low Stock stacked --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

        <div class="card">
            <div class="card-header">
                <span class="card-title">🏆 TOP PRODUCTS</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>PRODUCT</th><th>CATEGORY</th><th>SOLD</th></tr>
                    </thead>
                    <tbody>
                        @forelse($top_products as $product)
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    @php
                                        $thumbSrc = $product->image
                                            ? (Str::startsWith($product->image, ['http://','https://'])
                                                ? $product->image
                                                : asset(ltrim($product->image,'/')))
                                            : null;
                                    @endphp
                                    @if($thumbSrc)
                                        <img src="{{ $thumbSrc }}" class="product-thumb" alt="" onerror="this.style.display='none'" />
                                    @else
                                        <div class="product-thumb" style="display:flex; align-items:center; justify-content:center; font-size:16px;">📦</div>
                                    @endif
                                    <span style="font-weight:600;">{{ Str::limit($product->name, 22) }}</span>
                                </div>
                            </td>
                            <td><span class="badge badge-orange">{{ $product->category }}</span></td>
                            <td style="color:#F97316; font-weight:700;">{{ $product->order_items_count }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align:center; color:rgba(255,255,255,0.3); padding:20px;">No data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="card-title">⚠ LOW STOCK</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>PRODUCT</th><th>STOCK</th></tr>
                    </thead>
                    <tbody>
                        @forelse($low_stock as $product)
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    @php
                                        $thumbSrc = $product->image
                                            ? (Str::startsWith($product->image, ['http://','https://'])
                                                ? $product->image
                                                : asset(ltrim($product->image,'/')))
                                            : null;
                                    @endphp
                                    @if($thumbSrc)
                                        <img src="{{ $thumbSrc }}" class="product-thumb" alt="" onerror="this.style.display='none'" />
                                    @else
                                        <div class="product-thumb" style="display:flex; align-items:center; justify-content:center; font-size:16px;">📦</div>
                                    @endif
                                    <span style="font-weight:600;">{{ Str::limit($product->name, 22) }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $product->stock == 0 ? 'badge-cancelled' : 'badge-pending' }}">
                                    {{ $product->stock }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" style="text-align:center; color:rgba(255,255,255,0.3); padding:20px;">All stocked ✓</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@endif
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
// ── Global defaults ────────────────────────────────────────────────────────────
Chart.defaults.color                             = 'rgba(255,255,255,0.45)';
Chart.defaults.borderColor                       = 'rgba(255,255,255,0.06)';
Chart.defaults.font.family                       = "'Rajdhani', sans-serif";
Chart.defaults.font.size                         = 12;
Chart.defaults.plugins.legend.labels.boxWidth    = 12;
Chart.defaults.plugins.legend.labels.padding     = 16;
Chart.defaults.plugins.tooltip.backgroundColor   = '#1A1A1A';
Chart.defaults.plugins.tooltip.borderColor       = 'rgba(249,115,22,0.4)';
Chart.defaults.plugins.tooltip.borderWidth       = 1;
Chart.defaults.plugins.tooltip.padding           = 10;
Chart.defaults.plugins.tooltip.titleColor        = '#F97316';
Chart.defaults.plugins.tooltip.bodyColor         = 'rgba(255,255,255,0.8)';

// ── Data from Laravel ──────────────────────────────────────────────────────────
const monthlyLabels   = @json($monthlyLabels);
const monthlyRevenue  = @json($monthlyRevenue);
const monthlyOrders   = @json($monthlyOrders);
const monthlyUsers    = @json($monthlyUserCounts);
const dailyLabels     = @json($dailyLabels);
const dailyRevenue    = @json($dailyRevenue);
const statusLabels    = @json($statusLabels);
const statusCounts    = @json($statusCounts);
const categoryLabels  = @json($categoryLabels);
const categoryRevData = @json($categoryRevData);

// ── Palette ────────────────────────────────────────────────────────────────────
const orange    = '#F97316';
const orangeMid = 'rgba(249,115,22,0.6)';
const blue      = '#3B82F6';
const green     = '#22C55E';
const yellow    = '#EAB308';
const red       = '#EF4444';
const purple    = '#A855F7';
const pieColors = [yellow, green, blue, purple, red, orange];

function makeGradient(ctx, top, bottom) {
    const g = ctx.createLinearGradient(0, 0, 0, 300);
    g.addColorStop(0, top);
    g.addColorStop(1, bottom);
    return g;
}

// 1. Monthly Revenue — Line
const rCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(rCtx, {
    type: 'line',
    data: { labels: monthlyLabels, datasets: [{ label:'Revenue ($)', data:monthlyRevenue,
        borderColor:orange, borderWidth:2.5, pointBackgroundColor:orange,
        pointBorderColor:'#111', pointBorderWidth:2, pointRadius:4, pointHoverRadius:7,
        fill:true, backgroundColor:makeGradient(rCtx,'rgba(249,115,22,0.25)','rgba(249,115,22,0)'), tension:0.4 }] },
    options: { responsive:true, plugins:{ legend:{display:false},
        tooltip:{ callbacks:{ label: c => ' $'+c.parsed.y.toLocaleString() }}},
        scales:{ x:{grid:{color:'rgba(255,255,255,0.04)'}}, y:{grid:{color:'rgba(255,255,255,0.04)'},
            ticks:{ callback: v => '$'+v.toLocaleString() }}}}
});

// 2. Monthly Orders — Bar
const oCtx = document.getElementById('ordersChart').getContext('2d');
new Chart(oCtx, {
    type: 'bar',
    data: { labels: monthlyLabels, datasets: [{ label:'Orders', data:monthlyOrders,
        backgroundColor:makeGradient(oCtx, orangeMid,'rgba(249,115,22,0.15)'),
        borderColor:orange, borderWidth:1.5, borderRadius:6, borderSkipped:false }] },
    options: { responsive:true, plugins:{legend:{display:false}},
        scales:{ x:{grid:{color:'rgba(255,255,255,0.04)'}}, y:{grid:{color:'rgba(255,255,255,0.04)'},
            ticks:{stepSize:1}}}}
});

// 3. Daily Sales — Line
const dCtx = document.getElementById('dailyChart').getContext('2d');
new Chart(dCtx, {
    type: 'line',
    data: { labels: dailyLabels, datasets: [{ label:'Revenue ($)', data:dailyRevenue,
        borderColor:blue, borderWidth:2.5, pointBackgroundColor:blue,
        pointBorderColor:'#111', pointBorderWidth:2, pointRadius:4, pointHoverRadius:7,
        fill:true, backgroundColor:makeGradient(dCtx,'rgba(59,130,246,0.25)','rgba(59,130,246,0)'), tension:0.4 }] },
    options: { responsive:true, plugins:{ legend:{display:false},
        tooltip:{ callbacks:{ label: c => ' $'+c.parsed.y.toLocaleString() }}},
        scales:{ x:{grid:{color:'rgba(255,255,255,0.04)'}}, y:{grid:{color:'rgba(255,255,255,0.04)'},
            ticks:{ callback: v => '$'+v.toLocaleString() }}}}
});

// 4. User Registrations — Bar
const uCtx = document.getElementById('usersChart').getContext('2d');
new Chart(uCtx, {
    type: 'bar',
    data: { labels: monthlyLabels, datasets: [{ label:'New Users', data:monthlyUsers,
        backgroundColor:makeGradient(uCtx,'rgba(34,197,94,0.6)','rgba(34,197,94,0.1)'),
        borderColor:green, borderWidth:1.5, borderRadius:6, borderSkipped:false }] },
    options: { responsive:true, plugins:{legend:{display:false}},
        scales:{ x:{grid:{color:'rgba(255,255,255,0.04)'}}, y:{grid:{color:'rgba(255,255,255,0.04)'},
            ticks:{stepSize:1}}}}
});

// 5. Order Status — Pie
new Chart(document.getElementById('statusChart').getContext('2d'), {
    type: 'pie',
    data: { labels: statusLabels.map(s => s.toUpperCase()),
        datasets:[{ data:statusCounts, backgroundColor:[yellow,green,blue,purple,red],
            borderColor:'#111', borderWidth:3, hoverOffset:8 }] },
    options: { responsive:true, plugins:{ legend:{position:'bottom',labels:{padding:14,font:{size:11}}},
        tooltip:{ callbacks:{ label: c => ' '+c.label+': '+c.parsed+' orders' }}}}
});

// 6. Revenue by Category — Doughnut
new Chart(document.getElementById('categoryChart').getContext('2d'), {
    type: 'doughnut',
    data: { labels: categoryLabels,
        datasets:[{ data:categoryRevData, backgroundColor:pieColors,
            borderColor:'#111', borderWidth:3, hoverOffset:8 }] },
    options: { responsive:true, cutout:'60%', plugins:{ legend:{position:'bottom',labels:{padding:14,font:{size:11}}},
        tooltip:{ callbacks:{ label: c => ' '+c.label+': $'+c.parsed.toLocaleString() }}}}
});
</script>
@endpush

@push('styles')
<style>
.chart-badge {
    font-size: 11px;
    color: rgba(255,255,255,0.35);
    background: rgba(255,255,255,0.05);
    padding: 3px 10px;
    border-radius: 20px;
    letter-spacing: 1px;
}
@media (max-width: 900px) {
    .chart-grid-2 { grid-template-columns: 1fr; }
}
</style>
@endpush