@extends('dashboard.layout')
@section('title', strtoupper(__('dashboard.nav.settings')))

@section('content')

    @include('dashboard._permission_check', ['feature' => 'settings'])
    @php $_permDenied = $GLOBALS['_tronmatix_perm_denied'] ?? false; @endphp

    @if (!$_permDenied)

        @php
            $s = $settings;
            function son($settings, $key)
            {
                return ($settings[$key] ?? '0') === '1';
            }
            function sval($settings, $key, $default = '')
            {
                return $settings[$key] ?? $default;
            }
        @endphp

        {{-- ── Save success toast ───────────────────────────────────────────────────── --}}
        @if (session('success'))
            <div id="save-toast"
                style="
                    position:fixed; top:20px; right:20px; z-index:9999;
                    padding:12px 20px; background:#22c55e; color:#fff;
                    border-radius:12px; box-shadow:0 10px 25px rgba(34,197,94,0.3);
                    font-family:Rajdhani, sans-serif; font-weight:700; letter-spacing:1px;
                    display:flex; align-items:center; gap:10px;
                    animation:toastIn .4s cubic-bezier(0.34,1.56,0.64,1);
                ">
                <span>✓</span> {{ strtoupper(__('dashboard.settings.saved')) }}
            </div>
            <style>
                @keyframes toastIn { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
                @keyframes toastOut { from { transform: translateY(0); opacity: 1; } to { transform: translateY(-20px); opacity: 0; } }
            </style>
            <script>
                setTimeout(() => {
                    const t = document.getElementById('save-toast');
                    if (t) {
                        t.style.animation = 'toastOut .3s ease-out forwards';
                        setTimeout(() => t.remove(), 300);
                    }
                }, 3000);
            </script>
        @endif

        <form method="POST" action="{{ route('dashboard.settings.update') }}" id="settings-form">
            @csrf @method('PUT')

            <div id="settings-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:20px; align-items:start;">
                <style>
                    @media (max-width: 900px) {
                        #settings-grid { grid-template-columns: 1fr !important; }
                    }
                </style>

                {{-- ════════════════════════ LEFT COLUMN ════════════════════════════════════ --}}
                <div style="display:flex; flex-direction:column; gap:20px;">

                    {{-- ── NOTIFICATIONS ────────────────────────────────────────────────────── --}}
                    <div class="card">
                        <div class="card-header">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div class="s-icon-box"
                                    style="background:rgba(249,115,22,0.1); border-color:rgba(249,115,22,0.25);">🔔</div>
                                <div>
                                    <div class="s-card-title">{{ strtoupper(__('dashboard.settings.notifications')) }}</div>
                                    <div class="s-card-sub">{{ __('dashboard.settings.alertControl') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" style="padding:8px 20px;">

                            {{-- Low stock --}}
                            <div class="s-row">
                                <div class="s-info">
                                    <div class="s-label">🟠 {{ __('dashboard.settings.lowStockAlert') }}</div>
                                    <div class="s-desc">{{ __('dashboard.settings.bellLowStock') }}</div>
                                </div>
                                <label class="ts">
                                    <input type="checkbox" name="notif_low_stock" id="chk-low-stock"
                                        {{ son($s, 'notif_low_stock') ? 'checked' : '' }}
                                        onchange="showSub('sub-threshold', this.checked)">
                                    <span class="ts-track"></span>
                                </label>
                            </div>
                            <div id="sub-threshold" class="s-sub {{ son($s, 'notif_low_stock') ? '' : 's-hidden' }}">
                                <div class="s-sub-label">{{ strtoupper(__('dashboard.form.stockAlert')) }}</div>
                                <div style="display:flex; align-items:center; gap:12px; margin-top:8px;">
                                    <input type="number" name="notif_low_stock_threshold"
                                        value="{{ sval($s, 'notif_low_stock_threshold', '5') }}" min="1"
                                        max="999" class="s-num-input">
                                    <span style="color:rgba(255,255,255,0.35); font-size: var(--title-size);">units</span>
                                    @if ($counts['low_stock'] > 0)
                                        <span
                                            style="font-size: var(--title-size); color:#F97316; background:rgba(249,115,22,0.1);
                        border:1px solid rgba(249,115,22,0.2); border-radius:20px; padding:2px 10px;">
                                            {{ $counts['low_stock'] }} {{ strtolower(__('dashboard.table.product')) }}(s) affected now
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="s-divider"></div>

                            {{-- New order --}}
                            <div class="s-row">
                                <div class="s-info">
                                    <div class="s-label">📦 {{ __('dashboard.settings.newOrderAlert') }}</div>
                                    <div class="s-desc">{{ __('dashboard.settings.bellNewOrder') }}</div>
                                </div>
                                <label class="ts">
                                    <input type="checkbox" name="notif_new_order"
                                        {{ son($s, 'notif_new_order') ? 'checked' : '' }}>
                                    <span class="ts-track"></span>
                                </label>
                            </div>
                            <div class="s-divider"></div>

                            {{-- Pending KHQR --}}
                            <div class="s-row">
                                <div class="s-info">
                                    <div class="s-label">📱 {{ __('dashboard.settings.khqrAwaiting') }}</div>
                                    <div class="s-desc">{{ __('dashboard.settings.bellKhqrAwaiting') }}</div>
                                </div>
                                <label class="ts">
                                    <input type="checkbox" name="notif_pending_payment"
                                        {{ son($s, 'notif_pending_payment') ? 'checked' : '' }}>
                                    <span class="ts-track"></span>
                                </label>
                            </div>
                            <div class="s-divider"></div>

                            {{-- QR confirmed --}}
                            <div class="s-row">
                                <div class="s-info">
                                    <div class="s-label">✅ {{ __('dashboard.settings.khqrConfirmed') }}</div>
                                    <div class="s-desc">{{ __('dashboard.settings.bellKhqrConfirmed') }}</div>
                                </div>
                                <label class="ts">
                                    <input type="checkbox" name="notif_qr_confirmed"
                                        {{ son($s, 'notif_qr_confirmed') ? 'checked' : '' }}>
                                    <span class="ts-track"></span>
                                </label>
                            </div>
                            <div class="s-divider"></div>

                            {{-- Delivery confirmed --}}
                            <div class="s-row">
                                <div class="s-info">
                                    <div class="s-label">🚚 {{ __('dashboard.settings.deliveryAlert') }}</div>
                                    <div class="s-desc">{{ __('dashboard.settings.bellDeliveryAlert') }}</div>
                                </div>
                                <label class="ts">
                                    <input type="checkbox" name="notif_delivery_confirm"
                                        {{ son($s, 'notif_delivery_confirm') ? 'checked' : '' }}>
                                    <span class="ts-track"></span>
                                </label>
                            </div>

                        </div>
                    </div>

                    {{-- ── STORE ────────────────────────────────────────────────────────────── --}}
                    <div class="card">
                        <div class="card-header">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div class="s-icon-box"
                                    style="background:rgba(59,130,246,0.1); border-color:rgba(59,130,246,0.25);">🏪</div>
                                <div>
                                    <div class="s-card-title">{{ strtoupper(__('dashboard.settings.store')) }}</div>
                                    <div class="s-card-sub">{{ __('dashboard.settings.storeIdentity') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" style="padding:8px 20px;">

                            {{-- Store open toggle --}}
                            <div class="s-row">
                                <div class="s-info">
                                    <div class="s-label">🟢 {{ __('dashboard.settings.storeOpen') }}</div>
                                    <div class="s-desc">{{ __('dashboard.settings.storeOpenDesc') }}
                                    </div>
                                </div>
                                <label class="ts">
                                    <input type="checkbox" name="store_open" {{ son($s, 'store_open') ? 'checked' : '' }}>
                                    <span class="ts-track ts-green"></span>
                                </label>
                            </div>
                            <div class="s-divider"></div>

                            <div style="padding:12px 0 4px;">
                                <div class="s-sub-label">{{ strtoupper(__('dashboard.settings.storeDisplayName')) }}</div>
                                <input type="text" name="store_name"
                                    value="{{ sval($s, 'store_name', 'Tronmatix Computer') }}" class="s-input"
                                    style="margin-top:8px;" placeholder="e.g. Tronmatix Computer" />
                            </div>

                            <div style="padding:12px 0 4px;">
                                <div class="s-sub-label">{{ strtoupper(__('dashboard.settings.defaultCurrency')) }}</div>
                                <select name="store_currency" class="s-input" style="margin-top:8px; cursor:pointer;">
                                    @foreach (['USD' => '🇺🇸 USD — US Dollar', 'KHR' => '🇰🇭 KHR — Khmer Riel', 'EUR' => '🇪🇺 EUR — Euro', 'SGD' => '🇸🇬 SGD — Singapore Dollar'] as $code => $label)
                                        <option value="{{ $code }}"
                                            {{ sval($s, 'store_currency', 'USD') === $code ? 'selected' : '' }}>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                    </div>

                    {{-- ── TELEGRAM MARQUEE ─────────────────────────────────────────────────── --}}
                    <div class="card">
                        <div class="card-header">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div class="s-icon-box"
                                    style="background:rgba(249,115,22,0.1); border-color:rgba(249,115,22,0.25);">📢</div>
                                <div>
                                    <div class="s-card-title">TELEGRAM MARQUEE</div>
                                    <div class="s-card-sub">Edit marquee text shown to users (English & Khmer)</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" style="padding:16px 20px;">
                            @forelse($marqueeMessages as $msg)
                                <form method="POST" action="{{ route('dashboard.settings.marquees.update', $msg->id) }}"
                                    style="margin-bottom:16px; padding:16px; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.06); border-radius:12px;">
                                    @csrf @method('PUT')
                                    <div style="display:flex; flex-direction:column; gap:12px;">
                                        <div>
                                            <div class="s-sub-label">ROUTE</div>
                                            <input type="text" name="route" value="{{ $msg->route ?? '' }}"
                                                class="s-input"
                                                style="margin-top:6px; font-size:13px; color:rgba(255,255,255,0.5);"
                                                placeholder="e.g. /cart or leave empty for all pages">
                                        </div>
                                        <div>
                                            <div class="s-sub-label">ENGLISH TEXT</div>
                                            <textarea name="text_en" rows="2" class="s-input" style="margin-top:6px; resize:vertical;">{{ $msg->text_en }}</textarea>
                                        </div>
                                        <div>
                                            <div class="s-sub-label">KHMER TEXT</div>
                                            <textarea name="text_kh" rows="2" class="s-input" style="margin-top:6px; resize:vertical;">{{ $msg->text_kh }}</textarea>
                                        </div>
                                        <div style="display:flex; justify-content:flex-end;">
                                            <button type="submit"
                                                style="
                            padding:8px 20px; border-radius:8px; border:none; cursor:pointer;
                            background:linear-gradient(135deg,#F97316,#ea580c); color:#fff;
                            font-family:Rajdhani, var(--font-kh), sans-serif; font-size: var(--title-size); font-weight:700;
                            letter-spacing:1px; transition:all .2s;
                        "
                                                onmouseover="this.style.transform='translateY(-1px)'"
                                                onmouseout="this.style.transform='none'">
                                                💾 {{ strtoupper(__('dashboard.btn.saveSettings')) }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @empty
                                <div
                                    style="text-align:center; padding:20px; color:rgba(255,255,255,0.3); font-size: var(--title-size);">
                                    No marquee messages yet.
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>

                {{-- ════════════════════════ RIGHT COLUMN ═══════════════════════════════════ --}}
                <div style="display:flex; flex-direction:column; gap:20px;">

                    {{-- ── LIVE ALERTS PANEL ────────────────────────────────────────────────── --}}
                    <div class="card" style="border-color:rgba(249,115,22,0.18);">
                        <div class="card-header" style="border-color:rgba(249,115,22,0.12);">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div class="s-icon-box"
                                    style="background:rgba(249,115,22,0.1); border-color:rgba(249,115,22,0.25);">📊</div>
                                <div>
                                    <div class="s-card-title" style="color:#F97316;">{{ strtoupper(__('dashboard.settings.liveAlerts')) }}</div>
                                    <div class="s-card-sub">Click any card to jump to the relevant page</div>
                                </div>
                            </div>
                            <a href="{{ route('dashboard.settings') }}"
                                style="
                font-size: var(--title-size); color:rgba(255,255,255,0.3); text-decoration:none;
                background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08);
                border-radius:6px; padding:4px 10px; letter-spacing:1px;
                transition:color .2s;"
                                onmouseover="this.style.color='#F97316'"
                                onmouseout="this.style.color='rgba(255,255,255,0.3)'">
                                ↺ REFRESH
                            </a>
                        </div>
                        <div class="card-body" style="padding:16px 20px;">
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">

                                @php
                                    $_ls = __('dashboard.settings.lowStockAlert');
                                    $_no = __('dashboard.settings.newOrderAlert');
                                    $_aw = __('dashboard.settings.khqrAwaiting');
                                    $_qr = __('dashboard.settings.khqrConfirmed');
                                    $_dl = __('dashboard.settings.deliveryAlert');
                                    $alertGrid = [[$_ls, $counts['low_stock'], '🟠', '#F97316', 'rgba(249,115,22,.12)', 'rgba(249,115,22,.25)', route('dashboard.products')], [$_no, $counts['pending_orders'], '📦', '#eab308', 'rgba(234,179,8,.12)', 'rgba(234,179,8,.25)', route('dashboard.orders', ['status' => 'pending'])], [$_aw, $counts['pending_payment'], '📱', '#3b82f6', 'rgba(59,130,246,.12)', 'rgba(59,130,246,.25)', route('dashboard.orders')], [$_qr, $counts['qr_confirmed'], '✅', '#22c55e', 'rgba(34,197,94,.12)', 'rgba(34,197,94,.25)', route('dashboard.orders')], [$_dl, $counts['delivered_today'], '🚚', '#a78bfa', 'rgba(167,139,250,.12)', 'rgba(167,139,250,.25)', route('dashboard.orders', ['status' => 'delivered'])]];
                                @endphp

                                @foreach ($alertGrid as [$label, $count, $icon, $color, $bg, $border, $url])
                                    <a href="{{ $url }}"
                                        style="
                    display:block; text-decoration:none; padding:14px 16px;
                    background:{{ $bg }}; border:1.5px solid {{ $count > 0 ? $border : 'rgba(255,255,255,0.06)' }};
                    border-radius:14px; transition:transform .15s, box-shadow .15s;
                    {{ $count > 0 ? 'box-shadow:0 0 16px ' . $bg . ';' : '' }}
                "
                                        onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 24px rgba(0,0,0,0.3)'"
                                        onmouseout="this.style.transform='none'; this.style.boxShadow='{{ $count > 0 ? '0 0 16px ' . $bg : 'none' }}'">
                                        <div style="font-size: var(--title-size); margin-bottom:4px;">{{ $icon }}
                                        </div>
                                        <div
                                            style="font-size: var(--title-size); font-weight:900; color:{{ $count > 0 ? $color : 'rgba(255,255,255,0.2)' }};
                         line-height:1; font-family:Rajdhani, var(--font-kh), sans-serif;">
                                            {{ $count }}</div>
                                        <div
                                            style="font-size: var(--title-size); color:rgba(255,255,255,0.35); margin-top:5px; letter-spacing:0.5px;">
                                            {{ $label }}</div>
                                    </a>
                                @endforeach

                                {{-- Extra "all good" card if everything is 0 --}}
                                @if (array_sum(array_column($alertGrid, 1)) === 0)
                                    <div
                                        style="grid-column:1/-1; text-align:center; padding:20px;
                    background:rgba(34,197,94,0.05); border:1px solid rgba(34,197,94,0.15); border-radius:14px;">
                                        <div style="font-size: var(--title-size); margin-bottom:6px;">🎉</div>
                                        <div
                                            style="color:#22c55e; font-weight:700; font-size: var(--title-size); letter-spacing:1px;">
                                            All Clear!</div>
                                        <div
                                            style="color:rgba(255,255,255,0.3); font-size: var(--title-size); margin-top:4px;">
                                            No alerts at the moment</div>
                                    </div>
                                @endif

                            </div>
                            <div
                                style="margin-top:10px; font-size: var(--title-size); color:rgba(255,255,255,0.2); text-align:right;">
                                As of {{ now()->format('d M Y, H:i:s') }}
                            </div>
                        </div>
                    </div>

                    {{-- ── ORDER AUTOMATION ─────────────────────────────────────────────────── --}}
                    <div class="card">
                        <div class="card-header">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div class="s-icon-box"
                                    style="background:rgba(167,139,250,0.1); border-color:rgba(167,139,250,0.25);">⚙️</div>
                                <div>
                                    <div class="s-card-title">{{ strtoupper(__('dashboard.settings.orderAutomation')) }}</div>
                                    <div class="s-card-sub">Rules that run automatically on orders</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" style="padding:8px 20px;">

                            {{-- Auto-confirm cash --}}
                            <div class="s-row">
                                <div class="s-info">
                                    <div class="s-label">💵 Auto-confirm Cash Orders</div>
                                    <div class="s-desc">Automatically move COD orders from <em>Pending</em> →
                                        <em>Confirmed</em> immediately after placement
                                    </div>
                                </div>
                                <label class="ts">
                                    <input type="checkbox" name="order_auto_confirm_cash"
                                        {{ son($s, 'order_auto_confirm_cash') ? 'checked' : '' }}>
                                    <span class="ts-track"></span>
                                </label>
                            </div>
                            <div class="s-divider"></div>

                            {{-- Auto-cancel stale orders --}}
                            <div class="s-row" style="align-items:flex-start; padding-top:16px; padding-bottom:16px;">
                                <div class="s-info">
                                    <div class="s-label">🗑️ Auto-cancel Stale Orders</div>
                                    <div class="s-desc">Cancel <em>Pending</em> orders that haven't been touched after N
                                        hours.<br>Set to <strong style="color:#fff;">0</strong> to disable.</div>
                                </div>
                                <div style="display:flex; align-items:center; gap:8px; flex-shrink:0; padding-top:4px;">
                                    <input type="number" name="order_auto_cancel_hours"
                                        value="{{ sval($s, 'order_auto_cancel_hours', '0') }}" min="0"
                                        max="720" class="s-num-input">
                                    <span
                                        style="color:rgba(255,255,255,0.3); font-size: var(--title-size); white-space:nowrap;">hrs</span>
                                </div>
                            </div>

                            {{-- ── DISPLAY ──────────────────────────────────────────────────────────── --}}
                            <div class="card">
                                <div class="card-header">
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <div class="s-icon-box"
                                            style="background:rgba(34,197,94,0.1); border-color:rgba(34,197,94,0.25);">🖥️
                                        </div>
                                        <div>
                                            <div class="s-card-title">{{ strtoupper(__('dashboard.settings.display')) }}</div>
                                            <div class="s-card-sub">Pagination size for orders and products</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body" style="padding:16px 20px;">
                                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
                                        <div>
                                            <div class="s-sub-label">{{ strtoupper(__('dashboard.form.ordersPerPage')) }}</div>
                                            <input type="number" name="dashboard_rows_per_page"
                                                value="{{ sval($s, 'dashboard_rows_per_page', '20') }}" min="5"
                                                max="200" class="s-input"
                                                style="margin-top:8px; text-align:center;">
                                        </div>
                                        <div>
                                            <div class="s-sub-label">{{ strtoupper(__('dashboard.form.productsPerPage')) }}</div>
                                            <input type="number" name="products_per_page"
                                                value="{{ sval($s, 'products_per_page', '12') }}" min="4"
                                                max="100" class="s-input"
                                                style="margin-top:8px; text-align:center;">
                                        </div>
                                    </div>

                                    <div class="s-divider" style="margin:16px 0;"></div>

                                    <div>
                                        <div class="s-sub-label">⭐ VIP SPENDING GOAL ($)</div>
                                        <div
                                            style="font-size: var(--title-size); color:var(--text-muted, rgba(60,60,60,0.7)); margin-top:4px; margin-bottom:10px;">
                                            Minimum total spent required for a customer to earn <strong
                                                style="color:#F97316;">VIP</strong> status
                                        </div>
                                        <input type="number" name="vip_threshold"
                                            value="{{ sval($s, 'vip_threshold', '5000') }}" min="0"
                                            step="1" class="s-input" style="max-width:220px; text-align:center;">
                                    </div>
                                </div>
                            </div>

                            {{-- ── SAVE / RESET buttons ─────────────────────────────────────────────── --}}
                            <div style="display:flex; gap:12px; margin-top:20px;">
                                <button type="submit" id="save-btn"
                                    style="
            flex:2; padding:15px; border-radius:12px; border:none; cursor:pointer;
            background:linear-gradient(135deg,#F97316,#ea580c); color:#fff;
            font-family:Rajdhani, var(--font-kh), sans-serif; font-size: var(--title-size); font-weight:800;
            letter-spacing:2px; box-shadow:0 4px 20px rgba(249,115,22,0.35);
            transition:all .2s; display:flex; align-items:center; justify-content:center; gap:8px;
        "
                                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 28px rgba(249,115,22,0.5)'"
                                    onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 20px rgba(249,115,22,0.35)'">
                                    <span id="save-icon">💾</span>
                                    <span id="save-text">{{ strtoupper(__('dashboard.btn.saveSettings')) }}</span>
                                </button>
                                <a href="{{ route('dashboard.settings.reset') }}"
                                    onclick="return confirm('Reset all settings to factory defaults?')"
                                    style="
            flex:1; padding:15px; border-radius:12px; text-align:center; text-decoration:none;
            border:1.5px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04);
            color:rgba(255,255,255,0.4); font-family:Rajdhani, var(--font-kh), sans-serif;
            font-size: var(--title-size); font-weight:700; letter-spacing:1px;
            display:flex; align-items:center; justify-content:center; gap:6px;
            transition:all .2s;"
                                    onmouseover="this.style.borderColor='rgba(239,68,68,0.4)'; this.style.color='#ef4444'; this.style.background='rgba(239,68,68,0.06)'"
                                    onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.color='rgba(255,255,255,0.4)'; this.style.background='rgba(255,255,255,0.04)'">
                                    🔄 {{ strtoupper(__('dashboard.settings.reset')) }}
                                </a>
                            </div>
                        </div>
                    </div> <!-- close grid -->
                </div>
            </div> <!-- close #settings-grid -->
        </form>

        {{-- ════════════════════════════════════════════════════════════════════════════
        {{ strtoupper(__('dashboard.settings.rolePermissions')) }}
        ════════════════════════════════════════════════════════════════════════════ --}}
        @php
            $user = Auth::guard('admin')->user() ?? Auth::guard('staff')->user();
            $currentAdminRole = $user?->role ?? 'editor';
            $canEditPerms = in_array($currentAdminRole, ['admin', 'superadmin']);

            // ── Dynamic feature list from DB ─────────────────────────────────────
            $allFeatures = \App\Models\Feature::ordered()->get();
            $permFeatures = $allFeatures->mapWithKeys(fn ($f) => [
                $f->key => ['label' => $f->label, 'icon' => $f->icon],
            ])->toArray();

            // ── Dynamic role list from DB (superadmin excluded — always full access) ──
            $allRoles = \App\Models\Role::editable()->get();
            $permRoles = $allRoles->mapWithKeys(fn ($r) => [
                $r->key => ['label' => $r->label, 'color' => $r->color, 'icon' => $r->icon, 'description' => $r->description],
            ])->toArray();

            // Preload role lock configs for the template
            $roleLockConfig = $allRoles->mapWithKeys(fn ($r) => [
                $r->key => [
                    'locked_on'    => $r->locked_features ?? [],
                    'locked_off'   => $r->forbidden_features ?? [],
                ],
            ])->toArray();

            // ── Helper: resolve permission value for a role+feature ──────────────
            $perm = function ($role, $feature) use ($s, $roleLockConfig) {
                $key = "perm_{$role}_{$feature}";

                // 1) Check saved admin_settings value
                if (isset($s[$key])) {
                    return $s[$key] === '1';
                }

                // 2) Check role's locked_features (default ON)
                $lockedOn = in_array($feature, $roleLockConfig[$role]['locked_on'] ?? [], true);
                if ($lockedOn) {
                    return true;
                }

                // 3) Check role's forbidden_features (default OFF)
                $lockedOff = in_array($feature, $roleLockConfig[$role]['locked_off'] ?? [], true);
                if ($lockedOff) {
                    return false;
                }

                // 4) New combination — default OFF
                return false;
            };
        @endphp

        <div style="margin:24px auto 0; width:100%;">

            {{-- Section header --}}
            <div
                style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap:wrap; gap:12px;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div
                        style="width:42px; height:42px; border-radius:12px; background:rgba(167,139,250,0.12);
                        border:1px solid rgba(167,139,250,0.3); display:flex; align-items:center; justify-content:center; font-size: var(--title-size);">
                        🔐</div>
                    <div>
                        <div
                            style="font-size: var(--title-size); font-weight:800; letter-spacing:2px; color:var(--text-main, #fff);">
                            {{ strtoupper(__('dashboard.settings.rolePermissions')) }}</div>
                        <div
                            style="font-size: var(--title-size); color:var(--text-muted, rgba(255,255,255,0.35)); margin-top:2px;">
                            {{ __('dashboard.settings.permDesc') }}
                        </div>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <a href="{{ route('dashboard.staff') }}"
                        style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px;
                      border-radius:8px; border:1px solid rgba(167,139,250,0.3); background:rgba(167,139,250,0.08);
                      color:#a78bfa; font-size: var(--title-size); font-weight:700; letter-spacing:1px; text-decoration:none;
                      transition:all .2s;"
                        onmouseover="this.style.background='rgba(167,139,250,0.16)'"
                        onmouseout="this.style.background='rgba(167,139,250,0.08)'">
                        👥 {{ strtoupper(__('dashboard.nav.staff')) }}
                    </a>
                    @if (!$canEditPerms)
                        <div
                            style="display:flex; align-items:center; gap:6px; padding:8px 14px; border-radius:8px;
                        background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2);">
                            <span style="font-size: var(--title-size);">🔒</span>
                            <span
                                style="font-size: var(--title-size); color:#ef4444; font-weight:700; letter-spacing:1px;">{{ strtoupper(__('dashboard.settings.adminOnly')) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Permission matrix card --}}
            <div class="card" style="{{ !$canEditPerms ? 'opacity:0.75; pointer-events:none;' : '' }} width:100%;">
                <form method="POST" action="{{ route('dashboard.settings.permissions') }}" id="perms-form">
                    @csrf @method('PUT')

                    <div class="perm-table-wrapper" style="overflow-x:auto; overflow-y:visible; width:100%;">
                        <table style="border-collapse:collapse; min-width:100%;">
                            <thead>
                                <tr style="border-bottom:1px solid rgba(255,255,255,0.07);">
                                    <th
                                        style="padding:16px 20px; text-align:left; font-size: var(--title-size); letter-spacing:2px;
                                       color:var(--text-muted, rgba(255,255,255,0.35)); white-space:nowrap; font-weight:700; width:220px;">
                                        {{ strtoupper(__('dashboard.settings.featureModule')) }}
                                    </th>

                                    {{-- Superadmin: always full --}}
                                    <th style="padding:16px 14px; text-align:center; white-space:nowrap; width:12%;">
                                        <div
                                            style="display:inline-flex; flex-direction:column; align-items:center; gap:4px;">
                                            <div
                                                style="width:36px; height:36px; border-radius:10px;
                                                background:rgba(249,115,22,0.15); border:1px solid rgba(249,115,22,0.3);
                                                display:flex; align-items:center; justify-content:center; font-size: var(--title-size);">
                                                👑</div>
                                            <span
                                                style="font-size: var(--title-size); letter-spacing:1.5px; color:#F97316; font-weight:800;">{{ strtoupper(__('dashboard.settings.superAdminShort')) }}</span>
                                        </div>
                                    </th>

                                    @foreach ($permRoles as $roleKey => $roleMeta)
                                        <th style="padding:16px 8px; text-align:center; white-space:nowrap; width:12%;">
                                            <div
                                                style="display:inline-flex; flex-direction:column; align-items:center; gap:3px;">
                                                <div
                                                    style="width:32px; height:32px; border-radius:8px;
                                                background:{{ $roleMeta['color'] }}18; border:1px solid {{ $roleMeta['color'] }}44;
                                                display:flex; align-items:center; justify-content:center; font-size: var(--text-sm);">
                                                    {{ $roleMeta['icon'] }}
                                                </div>
                                                <span
                                                    style="font-size:10px; letter-spacing:0.8px; color:{{ $roleMeta['color'] }}; font-weight:800; line-height:1.1;">
                                                    {{ strtoupper($roleMeta['label']) }}
                                                </span>
                                                @if (!empty($roleMeta['description']))
                                                    <span
                                                        style="font-size:9px; color:rgba(255,255,255,0.3); line-height:1.2; max-width:90px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
                                                        title="{{ $roleMeta['description'] }}">
                                                        {{ $roleMeta['description'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($permFeatures as $featureKey => $featureMeta)
                                    <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                                        <td style="padding:14px 20px;">
                                            <div style="display:flex; align-items:center; gap:10px;">
                                                <span
                                                    style="font-size: var(--title-size);">{{ $featureMeta['icon'] }}</span>
                                                <span
                                                    style="font-size: var(--title-size); font-weight:600; color:var(--text-main, rgba(255,255,255,0.85));">
                                                    {{ $featureMeta['label'] }}
                                                </span>
                                            </div>
                                        </td>
                                        {{-- Superadmin always ✅ --}}
                                        <td style="padding:14px; text-align:center; vertical-align:middle;">
                                            <span
                                                style="display:inline-flex; align-items:center; justify-content:center;
                                             width:28px; height:28px; border-radius:8px;
                                             background:rgba(249,115,22,0.12); border:1px solid rgba(249,115,22,0.3);
                                             margin: 0 auto;">
                                                <svg width="14" height="14" fill="none" stroke="#F97316"
                                                    stroke-width="2.5" viewBox="0 0 24 24">
                                                    <polyline points="20 6 9 17 4 12" />
                                                </svg>
                                            </span>
                                        </td>

                                        @foreach ($permRoles as $roleKey => $roleMeta)
                                            @php
                                                $checked     = $perm($roleKey, $featureKey);
                                                $lockedOn    = in_array($featureKey, $roleLockConfig[$roleKey]['locked_on'] ?? [], true);
                                                $lockedOff   = in_array($featureKey, $roleLockConfig[$roleKey]['locked_off'] ?? [], true);
                                            @endphp
                                            <td style="padding:14px; text-align:center; vertical-align:middle;">
                                                @if ($lockedOn)
                                                    {{-- Hidden forced value: 1 --}}
                                                    <input type="hidden"
                                                        name="perm_{{ $roleKey }}_{{ $featureKey }}"
                                                        value="1">
                                                    <span
                                                        style="display:inline-flex; align-items:center; justify-content:center;
                                                 width:28px; height:28px; border-radius:8px;
                                                 background:rgba(249,115,22,0.12); border:1px solid rgba(249,115,22,0.3);
                                                 margin: 0 auto; display: flex;"
                                                        title="Admin always has this permission">
                                                        <svg width="14" height="14" fill="none"
                                                            stroke="#F97316" stroke-width="2.5" viewBox="0 0 24 24">
                                                            <polyline points="20 6 9 17 4 12" />
                                                        </svg>
                                                    </span>
                                                @elseif($lockedOff)
                                                    {{-- Hidden forced value: 0 — this role can NEVER get these pages --}}
                                                    <input type="hidden"
                                                        name="perm_{{ $roleKey }}_{{ $featureKey }}"
                                                        value="0">
                                                    <span
                                                        style="display:inline-flex; align-items:center; justify-content:center;
                                                 width:28px; height:28px; border-radius:8px;
                                                 background:rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.2);
                                                 margin: 0 auto; display: flex;"
                                                        title="This role cannot access this page">
                                                        <svg width="14" height="14" fill="none"
                                                            stroke="rgba(239,68,68,0.5)" stroke-width="2.5"
                                                            viewBox="0 0 24 24">
                                                            <line x1="18" y1="6" x2="6"
                                                                y2="18" />
                                                            <line x1="6" y1="6" x2="18"
                                                                y2="18" />
                                                        </svg>
                                                    </span>
                                                @else
                                                    <label class="perm-toggle"
                                                        style="cursor:{{ $canEditPerms ? 'pointer' : 'not-allowed' }};
                                                           display: flex; align-items: center; justify-content: center;
                                                           margin: 0 auto;">
                                                        <input type="checkbox"
                                                            name="perm_{{ $roleKey }}_{{ $featureKey }}"
                                                            value="1" {{ $checked ? 'checked' : '' }}
                                                            {{ !$canEditPerms ? 'disabled' : '' }}
                                                            onchange="markPermDirty()" style="display:none;" />
                                                        <span class="perm-check {{ $checked ? 'perm-on' : 'perm-off' }}"
                                                            data-color="{{ $roleMeta['color'] }}"
                                                            style="display: inline-flex; align-items: center; justify-content: center;"></span>
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
                    @if ($canEditPerms)
                        <div id="perm-save-bar"
                            style="
                padding:16px 20px; border-top:1px solid rgba(255,255,255,0.07);
                display:flex; align-items:center; justify-content:space-between; gap:12px;
                background:rgba(167,139,250,0.04);
                opacity:0; pointer-events:none; transition:opacity .25s;
            ">
                            <span style="font-size: var(--title-size); color:rgba(255,255,255,0.4);">
                                ⚠️ {{ __('dashboard.settings.unsavedPerms') }}
                            </span>
                            <div style="display:flex; gap:10px;">
                                <button type="button" onclick="resetPermissions()"
                                    style="padding:9px 18px; border-radius:8px; border:1px solid rgba(255,255,255,0.12);
                                   background:transparent; color:rgba(255,255,255,0.5);
                                   font-family:Rajdhani, var(--font-kh), sans-serif; font-size: var(--title-size); font-weight:700;
                                   letter-spacing:1px; cursor:pointer; transition:all .2s;"
                                    onmouseover="this.style.color='#fff'"
                                    onmouseout="this.style.color='rgba(255,255,255,0.5)'">
                                    DISCARD
                                </button>
                                <button type="submit" id="perm-save-btn"
                                    style="display:flex; align-items:center; gap:6px; padding:9px 20px;
                                   border-radius:8px; border:none;
                                   background:linear-gradient(135deg,#a78bfa,#7c3aed); color:#fff;
                                   font-family:Rajdhani, var(--font-kh), sans-serif; font-size: var(--title-size); font-weight:800;
                                   letter-spacing:1px; cursor:pointer; transition:all .2s;
                                   box-shadow:0 4px 16px rgba(167,139,250,0.3);">
                                    🔐 {{ strtoupper(__('dashboard.btn.saveSettings')) }}
                                </button>
                            </div>
                        </div>
                    @else
                        <div
                            style="padding:14px 20px; border-top:1px solid rgba(255,255,255,0.07);
                        display:flex; align-items:center; gap:8px;">
                            <span style="font-size: var(--title-size); color:rgba(239,68,68,0.7);">🔒</span>
                            <span style="font-size: var(--title-size); color:rgba(255,255,255,0.3);">
                                You need <strong style="color:#F97316;">Admin</strong> or
                                <strong style="color:#F97316;">Super Admin</strong> role to modify permissions.
                            </span>
                        </div>
                    @endif
                </form>
            </div>

            {{-- Add Role form (superadmin only) --}}
            @if ($canEditPerms)
                <div class="card" style="margin-top:18px; padding:18px 20px;">
                    <div style="font-size: var(--title-size); font-weight:800; letter-spacing:1.5px; color:var(--text-main, #fff); margin-bottom:12px;">
                        ➕ {{ strtoupper(__('dashboard.settings.addRole')) }}
                    </div>
                    <form method="POST" action="{{ route('dashboard.settings.roles.store') }}" style="display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end;">
                        @csrf
                        <div style="flex:1; min-width:140px;">
                            <label style="font-size:11px; color:var(--text-muted, rgba(255,255,255,0.4)); letter-spacing:1px; display:block; margin-bottom:4px;">KEY *</label>
                            <input type="text" name="key" required maxlength="50" placeholder="e.g. accountant"
                                style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-main, #fff); font-size:13px;" />
                        </div>
                        <div style="flex:1; min-width:140px;">
                            <label style="font-size:11px; color:var(--text-muted, rgba(255,255,255,0.4)); letter-spacing:1px; display:block; margin-bottom:4px;">LABEL *</label>
                            <input type="text" name="label" required maxlength="100" placeholder="Display name"
                                style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-main, #fff); font-size:13px;" />
                        </div>
                        <div style="width:60px;">
                            <label style="font-size:11px; color:var(--text-muted, rgba(255,255,255,0.4)); letter-spacing:1px; display:block; margin-bottom:4px;">COLOR</label>
                            <input type="color" name="color" value="#6b7280"
                                style="width:100%; height:36px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:transparent; cursor:pointer;" />
                        </div>
                        <div style="width:60px;">
                            <label style="font-size:11px; color:var(--text-muted, rgba(255,255,255,0.4)); letter-spacing:1px; display:block; margin-bottom:4px;">ICON</label>
                            <input type="text" name="icon" maxlength="50" placeholder="❓"
                                style="width:100%; padding:8px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-main, #fff); font-size:16px; text-align:center;" />
                        </div>
                        <div style="flex:2; min-width:180px;">
                            <label style="font-size:11px; color:var(--text-muted, rgba(255,255,255,0.4)); letter-spacing:1px; display:block; margin-bottom:4px;">DESCRIPTION</label>
                            <input type="text" name="description" maxlength="255" placeholder="Short description of this role"
                                style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-main, #fff); font-size:13px;" />
                        </div>
                        <div style="width:80px;">
                            <label style="font-size:11px; color:var(--text-muted, rgba(255,255,255,0.4)); letter-spacing:1px; display:block; margin-bottom:4px;">ORDER</label>
                            <input type="number" name="sort_order" value="0" min="0"
                                style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-main, #fff); font-size:13px;" />
                        </div>
                        <div style="display:flex; align-items:center; gap:6px; padding-bottom:8px;">
                            <input type="checkbox" name="is_staff_portal" id="role_staff_portal" checked
                                style="accent-color:#F97316;" />
                            <label for="role_staff_portal" style="font-size:12px; color:var(--text-muted, rgba(255,255,255,0.5)); cursor:pointer;">Staff portal</label>
                        </div>
                        <button type="submit"
                            style="padding:8px 18px; border-radius:8px; background:rgba(249,115,22,0.15); border:1px solid rgba(249,115,22,0.3); color:#F97316; font-weight:700; font-size:13px; cursor:pointer; letter-spacing:1px; white-space:nowrap;">
                            ADD ROLE
                        </button>
                    </form>
                </div>
            @endif

            {{-- Add Feature form (superadmin only) --}}
            @if ($canEditPerms)
                <div class="card" style="margin-top:14px; padding:18px 20px;">
                    <div style="font-size: var(--title-size); font-weight:800; letter-spacing:1.5px; color:var(--text-main, #fff); margin-bottom:12px;">
                        ➕ {{ strtoupper(__('dashboard.settings.addFeature')) }}
                    </div>
                    <form method="POST" action="{{ route('dashboard.settings.features.store') }}" style="display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end;">
                        @csrf
                        <div style="flex:1; min-width:140px;">
                            <label style="font-size:11px; color:var(--text-muted, rgba(255,255,255,0.4)); letter-spacing:1px; display:block; margin-bottom:4px;">KEY *</label>
                            <input type="text" name="key" required maxlength="50" placeholder="e.g. invoices"
                                style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-main, #fff); font-size:13px;" />
                        </div>
                        <div style="flex:1; min-width:140px;">
                            <label style="font-size:11px; color:var(--text-muted, rgba(255,255,255,0.4)); letter-spacing:1px; display:block; margin-bottom:4px;">LABEL *</label>
                            <input type="text" name="label" required maxlength="100" placeholder="Display name"
                                style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-main, #fff); font-size:13px;" />
                        </div>
                        <div style="width:60px;">
                            <label style="font-size:11px; color:var(--text-muted, rgba(255,255,255,0.4)); letter-spacing:1px; display:block; margin-bottom:4px;">ICON</label>
                            <input type="text" name="icon" maxlength="50" placeholder="📄"
                                style="width:100%; padding:8px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-main, #fff); font-size:16px; text-align:center;" />
                        </div>
                        <div style="width:140px;">
                            <label style="font-size:11px; color:var(--text-muted, rgba(255,255,255,0.4)); letter-spacing:1px; display:block; margin-bottom:4px;">CATEGORY</label>
                            <input type="text" name="category" maxlength="50" placeholder="e.g. admin, inventory"
                                style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-main, #fff); font-size:13px;" />
                        </div>
                        <div style="width:80px;">
                            <label style="font-size:11px; color:var(--text-muted, rgba(255,255,255,0.4)); letter-spacing:1px; display:block; margin-bottom:4px;">ORDER</label>
                            <input type="number" name="sort_order" value="0" min="0"
                                style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-main, #fff); font-size:13px;" />
                        </div>
                        <button type="submit"
                            style="padding:8px 18px; border-radius:8px; background:rgba(59,130,246,0.15); border:1px solid rgba(59,130,246,0.3); color:#3b82f6; font-weight:700; font-size:13px; cursor:pointer; letter-spacing:1px; white-space:nowrap;">
                            ADD FEATURE
                        </button>
                    </form>
                </div>
            @endif

            {{-- Manage existing roles/features (edit + delete) --}}
            @if ($canEditPerms && ($allRoles->count() > 0 || $allFeatures->count() > 0))
                <div class="card" style="margin-top:14px; padding:18px 20px;">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                        {{-- Roles management --}}
                        <div>
                            <div style="font-size: var(--title-size); font-weight:800; letter-spacing:1.5px; color:var(--text-main, #fff); margin-bottom:12px;">
                                🎭 ROLES
                            </div>
                            <div style="display:flex; flex-direction:column; gap:8px;">
                                @foreach ($allRoles as $r)
                                    <div style="display:flex; align-items:flex-start; gap:8px; padding:10px 12px; border-radius:8px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06);">
                                        <span style="font-size:18px; line-height:1.2;">{{ $r->icon }}</span>
                                        <div style="flex:1; min-width:0;">
                                            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                                <span style="font-size:13px; font-weight:700; color:{{ $r->color }};">{{ $r->label }}</span>
                                                <span style="font-size:10px; color:rgba(255,255,255,0.3); font-family:monospace;">{{ $r->key }}</span>
                                                @if($r->is_locked)<span style="font-size:10px; color:rgba(255,255,255,0.25);">🔒</span>@endif
                                                @if($r->is_staff_portal)<span style="font-size:10px; color:rgba(59,130,246,0.6);">staff</span>@endif
                                            </div>
                                            @if($r->description)
                                                <div style="font-size:11px; color:rgba(255,255,255,0.35); margin-top:3px; line-height:1.3;">{{ $r->description }}</div>
                                            @endif
                                        </div>
                                        @if (!$r->is_locked)
                                            <form method="POST" action="{{ route('dashboard.settings.roles.destroy', $r->id) }}" onsubmit="return confirm('Delete role {{ $r->key }}? This removes its permissions too.');" style="display:inline; flex-shrink:0;">
                                                @csrf @method('DELETE')
                                                <button type="submit" title="Delete role"
                                                    style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:14px; padding:2px 6px;">🗑️</button>
                                            </form>
                                        @else
                                            <span style="font-size:12px; color:rgba(255,255,255,0.2); flex-shrink:0;">🔒</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        {{-- Features management --}}
                        <div>
                            <div style="font-size: var(--title-size); font-weight:800; letter-spacing:1.5px; color:var(--text-main, #fff); margin-bottom:12px;">
                                📦 FEATURES
                            </div>
                            <div style="display:flex; flex-direction:column; gap:8px;">
                                @foreach ($allFeatures as $f)
                                    <div style="display:flex; align-items:center; gap:8px; padding:8px 12px; border-radius:8px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06);">
                                        <span style="font-size:18px;">{{ $f->icon }}</span>
                                        <span style="font-size:13px; font-weight:700; color:var(--text-main, #fff); flex:1;">{{ $f->label }}</span>
                                        <span style="font-size:11px; color:rgba(255,255,255,0.3);">{{ $f->key }}</span>
                                        <span style="font-size:10px; color:rgba(255,255,255,0.2);">{{ $f->category ?? '—' }}</span>
                                        <form method="POST" action="{{ route('dashboard.settings.features.destroy', $f->id) }}" onsubmit="return confirm('Delete feature {{ $f->key }}? This removes its permissions from all roles.');" style="display:inline;">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Delete feature"
                                                style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:14px; padding:2px 6px;">🗑️</button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Role legend --}}
            <div class="role-legend-grid" style="display:grid; grid-template-columns: repeat(6, 1fr); gap:12px; margin-top:14px;">
                @php
                    $legend = \App\Models\Role::ordered()->get()->map(fn ($r) => [
                        $r->icon,
                        strtoupper($r->label),
                        $r->color,
                        $r->key === 'superadmin' ? 'Full owner-level access to everything'
                            : ($r->key === 'admin' ? 'Full access; cannot demote superadmin'
                            : ($r->key === 'editor' ? 'Products, banners & discounts; read-only orders'
                            : ($r->key === 'seller' ? 'Products, orders & discounts management'
                            : ($r->key === 'delivery' ? 'Order delivery management only'
                            : 'Technical access; no admin-sensitive pages')))),
                    ])->toArray();
                @endphp
                <style>
                    /* Role legend cards — use the layout's real theme variables so
                       they adapt to dark AND light mode correctly. */
                    .role-legend-grid { transition: grid-template-columns .2s ease; }
                    .role-card {
                        display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: 12px;
                        background: var(--surface-2, #1a1a1a);
                        border: 1px solid var(--border, rgba(255,255,255,0.07));
                        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
                        flex: 1;
                        min-width: 0;
                    }
                    .role-desc { color: var(--text-muted, rgba(255,255,255,0.55)); }

                    /* 6 cols → 3 cols on tablet/mobile → 2 cols on very small screens */
                    @media (max-width: 1200px) {
                        .role-legend-grid { grid-template-columns: repeat(3, 1fr) !important; }
                    }
                    @media (max-width: 560px) {
                        .role-legend-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 8px; }
                        .role-card { flex-direction: column; align-items: flex-start; padding: 12px 14px; }
                    }
                    @media (max-width: 360px) {
                        .role-legend-grid { grid-template-columns: 1fr !important; }
                    }

                    /* Light mode overrides (layout sets vars via [data-theme="light"]) */
                    [data-theme='light'] .role-card {
                        background: #ffffff;
                        border: 1px solid rgba(15,23,42,0.10);
                        box-shadow: 0 2px 4px rgba(15,23,42,0.04);
                    }
                    [data-theme='light'] .role-desc { color: rgba(15,23,42,0.55); }
                </style>
                @foreach ($legend as [$icon, $label, $color, $desc])
                    <div class="role-card">
                        <span style="font-size: 24px; padding:8px; background:{{$color}}15; border-radius:8px;">{{ $icon }}</span>
                        <div style="min-width:0;">
                            <div style="font-size: 14px; font-weight:800; letter-spacing:0.5px; color:{{ $color }};">
                                {{ $label }}
                            </div>
                            <div class="role-desc" style="font-size: 13px; margin-top:2px;">
                                {{ $desc }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ── VIP MANAGEMENT ──────────────────────────────────────────────────────── --}}
            {{-- <div class="card" style="margin-top:20px;">
                <div class="card-header">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div class="s-icon-box"
                            style="background:rgba(249,115,22,0.1); border-color:rgba(249,115,22,0.25);">⭐</div>
                        <div>
                            <div class="s-card-title">{{ __('dashboard.settings.vipManagement') }}</div>
                            <div class="s-card-sub">{{ __('dashboard.settings.vipDemoteDesc') }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-body" style="padding:20px;">
                    <div
                        style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
                        <div>
                            <div
                                style="font-size: var(--title-size); font-weight:700; color:var(--text-main, #fff); margin-bottom:4px;">
                                {{ __('dashboard.settings.resetVipRoles') }}
                            </div>
                            <div
                                style="font-size: var(--title-size); color:var(--text-muted, rgba(60,60,60,0.7)); max-width:480px;">
                                {{ __('dashboard.settings.vipDemoteAction') }}
                                <strong
                                    style="color:#F97316;">${{ number_format((float) ($s['vip_threshold'] ?? 5000), 0) }}</strong>
                                back to Customer.
                            </div>
                        </div>
                        <form method="POST" action="{{ route('dashboard.settings.reset-vip') }}"
                            onsubmit="return confirm('Demote all VIP users below ${{ number_format((float) ($s['vip_threshold'] ?? 5000), 0) }} to Customer? This cannot be undone.');">
                            @csrf
                            <button type="submit" id="reset-vip-btn"
                                style="
                        padding:12px 24px; border-radius:10px; border:none; cursor:pointer;
                        background:linear-gradient(135deg,#F97316,#ea580c); color:#fff;
                        font-family:Rajdhani, var(--font-kh), sans-serif; font-size: var(--title-size); font-weight:800;
                        letter-spacing:2px; box-shadow:0 4px 16px rgba(249,115,22,0.3);
                        transition:all .2s; display:flex; align-items:center; gap:8px;
                    "
                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px rgba(249,115,22,0.45)'"
                                onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 16px rgba(249,115,22,0.3)'">
                                ⚠️ {{ strtoupper(__('dashboard.settings.resetVipRoles')) }}
                            </button>
                        </form>
                    </div>
                </div>
            </div> --}}

        </div>

        {{-- ════════════════════════ STYLES ══════════════════════════════════════════ --}}
        <style>
            @keyframes sSubSlide {
                from {
                    opacity: 0;
                    transform: translateY(-6px)
                }

                to {
                    opacity: 1;
                    transform: translateY(0)
                }
            }

            /* Icon box */
            .s-icon-box {
                width: 38px;
                height: 38px;
                border-radius: 10px;
                flex-shrink: 0;
                border: 1px solid;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: var(--title-size);
            }

            /* Card titles */
            .s-card-title {
                font-size: var(--title-size);
                font-weight: 800;
                letter-spacing: 1.5px;
                padding: 0;
            }

            .s-card-sub {
                font-size: var(--title-size);
                color: var(--text-muted, rgba(80, 80, 80, 0.7));
                margin-top: 2px;
            }

            :lang(km) .s-card-title {
                font-size: var(--title-size);
                font-weight: 400;
                letter-spacing: 1px;
                padding: 0;
            }

            :lang(km) .s-card-sub {
                font-size: var(--title-size);
                color: var(--text-muted, rgba(80, 80, 80, 0.7));
                margin-top: 2px;
            }

            :lang(km) .s-label {
                font-size: var(--title-size);
                font-weight: 400;
                color: var(--text-main, #1a1a1a);
                margin-bottom: 3px;
            }

            /* Setting row */
            .s-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                padding: 14px 0;
            }

            .s-info {
                flex: 1;
                min-width: 0;
            }

            .s-label {
                font-size: var(--title-size);
                font-weight: 700;
                color: var(--text-main, #1a1a1a);
                margin-bottom: 3px;
            }

            .s-desc {
                font-size: var(--title-size);
                color: var(--text-muted, rgba(60, 60, 60, 0.7));
                line-height: 1.5;
            }

            .s-desc em {
                color: rgba(249, 115, 22, 0.7);
                font-style: normal;
            }

            .s-desc strong {
                color: var(--text-main, #1a1a1a);
            }

            .s-divider {
                height: 1px;
                background: var(--border-color, rgba(255, 255, 255, 0.055));
            }

            /* Sub option */
            .s-sub {
                margin: 4px 0 10px;
                padding: 12px 14px;
                background: rgba(249, 115, 22, 0.04);
                border: 1px solid rgba(249, 115, 22, 0.12);
                border-radius: 10px;
                animation: sSubSlide .2s ease;
            }

            .s-hidden {
                display: none;
            }

            .s-sub-label {
                font-size: var(--title-size);
                letter-spacing: 2px;
                color: var(--text-muted, rgba(60, 60, 60, 0.7));
                font-weight: 700;
            }

            /* Toggle switch */
            .ts {
                position: relative;
                width: 52px;
                height: 28px;
                flex-shrink: 0;
                cursor: pointer;
            }

            .ts input {
                opacity: 0;
                width: 0;
                height: 0;
                position: absolute;
            }

            .ts-track {
                position: absolute;
                inset: 0;
                border-radius: 28px;
                background: var(--border-color, rgba(255, 255, 255, 0.1));
                border: 1px solid var(--border-color, rgba(255, 255, 255, 0.1));
                transition: background .3s, border-color .3s, box-shadow .3s;
                cursor: pointer;
            }

            .ts-track::before {
                content: '';
                position: absolute;
                width: 20px;
                height: 20px;
                left: 3px;
                top: 3px;
                border-radius: 50%;
                background: #fff;
                transition: transform .3s cubic-bezier(0.34, 1.56, 0.64, 1);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            }

            .ts input:checked+.ts-track {
                background: #F97316;
                border-color: #F97316;
                box-shadow: 0 0 14px rgba(249, 115, 22, 0.45);
            }

            .ts input:checked+.ts-track::before {
                transform: translateX(24px);
            }

            /* Green variant */
            .ts input:checked+.ts-track.ts-green {
                background: #22c55e;
                border-color: #22c55e;
                box-shadow: 0 0 14px rgba(34, 197, 94, 0.45);
            }

            /* Inputs */
            .s-input {
                width: 100%;
                background: rgba(255, 255, 255, 0.07);
                border: 1.5px solid rgba(255, 255, 255, 0.1);
                color: #fff;
                border-radius: 10px;
                padding: 10px 14px;
                font-family: Rajdhani, sans-serif;
                font-size: var(--title-size);
                font-weight: 600;
                outline: none;
                transition: border-color .2s;
            }

            .s-input:focus {
                border-color: #F97316;
            }

            .s-input option {
                background: #1a1a1a;
            }

            .s-num-input {
                width: 72px;
                background: rgba(255, 255, 255, 0.07);
                border: 1.5px solid rgba(255, 255, 255, 0.1);
                color: #fff;
                border-radius: 8px;
                padding: 7px 10px;
                font-family: Rajdhani, sans-serif;
                font-size: var(--title-size);
                font-weight: 700;
                outline: none;
                text-align: center;
                transition: border-color .2s;
            }

            .s-num-input:focus {
                border-color: #F97316;
            }
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
                const btn = document.getElementById('save-btn');
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
                if (bar) {
                    bar.style.opacity = '1';
                    bar.style.pointerEvents = 'auto';
                }
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
                if (bar) {
                    bar.style.opacity = '0';
                    bar.style.pointerEvents = 'none';
                }
            }

            // Loading state for permissions save
            const permsForm = document.getElementById('perms-form');
            if (permsForm) {
                permsForm.addEventListener('submit', function() {
                    const btn = document.getElementById('perm-save-btn');
                    if (btn) {
                        btn.textContent = '⏳ SAVING...';
                        btn.disabled = true;
                    }
                });
            }
        </script>

        <style>
            /* ── Permission toggle checkboxes ───────────────────────────────────────── */
            .perm-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
                vertical-align: middle;
            }

            .perm-check {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 28px;
                height: 28px;
                border-radius: 8px;
                transition: background .18s, border-color .18s, transform .15s;
                --perm-color: #a78bfa;
                margin: 0 auto;
            }

            .perm-check.perm-on {
                background: rgba(var(--perm-rgb, 167, 139, 250), 0.15);
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
                background: rgba(255, 255, 255, 0.03);
                border: 1.5px solid rgba(255, 255, 255, 0.1);
            }

            .perm-toggle:hover .perm-check.perm-off {
                border-color: rgba(255, 255, 255, 0.25);
                background: rgba(255, 255, 255, 0.06);
            }

            .perm-toggle:hover .perm-check {
                transform: scale(1.1);
            }

            /* Responsive adjustments */
            @media (max-width: 1100px) {
                /* General responsive cleanup */
            }

            /* Role permissions language adjustments */
            :lang(km) .perm-table-wrapper th,
            :lang(km) .perm-table-wrapper td {
                font-weight: 400 !important;
            }

            @media (max-width: 768px) {
                .perm-table-wrapper table {
                    min-width: 520px;
                }
            }

            @media (max-width: 700px) {
                .perm-table-wrapper {
                    overflow-x: auto;
                    -webkit-overflow-scrolling: touch;
                }
            }

            @media (max-width: 540px) {
                div[style*="min-width:200px"] {
                    min-width: 100% !important;
                    flex: 1 1 100% !important;
                }
            }

        </style>


    @endif
@endsection
