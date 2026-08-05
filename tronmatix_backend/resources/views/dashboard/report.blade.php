@extends('dashboard.layout')
@section('title', strtoupper(__('dashboard.nav.reports')))

@section('content')

@include('dashboard._permission_check', ['feature' => 'report'])
@php $_permDenied = $GLOBALS['_tronmatix_perm_denied'] ?? false; @endphp

@if(!$_permDenied)

{{-- ── Page Header ──────────────────────────────────────────────────────────────── --}}
<div style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap;
            gap:16px; margin-bottom:20px;">
    <div style="display:flex; align-items:center; gap:14px;">
        <div style="width:48px; height:48px; border-radius:14px; background:rgba(249,115,22,0.12);
                    border:1px solid rgba(249,115,22,0.3); display:flex; align-items:center;
                    justify-content:center; font-size: var(--title-size);">📊</div>
        <div>
            <div style="font-size: var(--title-size); font-weight:900; letter-spacing:3px;">{{ strtoupper(__('dashboard.nav.reports')) }}</div>
            <div style="font-size: var(--title-size); color:var(--text-muted); margin-top:2px;">
                {{ __('dashboard.report.pageDesc') }}
            </div>
        </div>
    </div>
</div>

{{-- ── Period Selector + Month Selector ─────────────────────────────────────── ── --}}
@if(isset($month))
    <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:20px;">
        <div style="font-size: var(--title-size); font-weight:700; color:var(--text-muted); letter-spacing:1px; text-transform:uppercase;">
            PERIOD:
        </div>
        <select id="periodSelect" name="period" onchange="applyPeriod(this.value)"
                class="export-input export-select"
                style="padding:7px 30px 7px 12px; cursor:pointer;">
            <option value="about"       {{ ($period ?? 'this-month') === 'about' ? 'selected' : '' }}>📅 All-Time</option>
            <option value="today"       {{ ($period ?? 'this-month') === 'today' ? 'selected' : '' }}>🗓️ Today</option>
            <option value="this-month"  {{ ($period ?? 'this-month') === 'this-month' ? 'selected' : '' }}>📆 This Month</option>
            <option value="6-months"    {{ ($period ?? 'this-month') === '6-months' ? 'selected' : '' }}>🗓️ Last 6 Months</option>
            <option value="this-year"   {{ ($period ?? 'this-month') === 'this-year' ? 'selected' : '' }}>📅 This Year</option>
        </select>
    </div>

    <x-month-selector :month="$month" />

    {{-- ── Trend KPI Cards ─────────────────────────────────────────────────────── --}}
    <div class="stats-grid" style="margin-bottom:24px;">
        <x-kpi-card label="Orders" value="{{ number_format($orders['current']) }}" :trend="$orders" color="orange">
            <x-slot name="icon">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="20" height="20"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
            </x-slot>
        </x-kpi-card>
        <x-kpi-card :label="__('dashboard.stats.kpiRevenue')" value="{{ compact_number($revenue['current'], '$') }}" :trend="$revenue" color="green">
            <x-slot name="icon">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="20" height="20"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            </x-slot>
        </x-kpi-card>
        <x-kpi-card :label="__('dashboard.stats.kpiCustomers')" value="{{ number_format($customers['current']) }}" :trend="$customers" color="blue">
            <x-slot name="icon">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="20" height="20"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            </x-slot>
        </x-kpi-card>
        <x-kpi-card :label="__('dashboard.stats.totalProducts')" value="{{ number_format($stats['total_products']) }}" color="purple">
            <x-slot name="icon">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="20" height="20"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
            </x-slot>
        </x-kpi-card>
    </div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════════
     SECTION 1 — EXPORT
══════════════════════════════════════════════════════════════════════════════ --}}
<div class="card" style="margin-bottom:24px; border-color:rgba(249,115,22,0.2);">
    <div class="card-header">
        <div style="display:flex; align-items:center; gap:10px;">
            <div style="width:36px; height:36px; border-radius:9px; background:rgba(249,115,22,0.1);
                        border:1px solid rgba(249,115,22,0.25); display:flex; align-items:center;
                        justify-content:center; font-size: var(--title-size); flex-shrink:0;">⬇</div>
            <div>
                <span class="card-title">{{ strtoupper(__('dashboard.report.exportData')) }}</span>
                <div style="font-size: var(--title-size); color:var(--text-muted); margin-top:1px;">
                    {{ __('dashboard.report.exportDesc') }}
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">

        {{-- Discount quick-stats row --}}
        <div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:20px;">
            <div style="background:rgba(168,85,247,0.08); border:1px solid rgba(168,85,247,0.2);
                 border-radius:10px; padding:14px 20px; flex:1; min-width:160px;">
                <div style="font-size: var(--title-size); color:rgba(255,255,255,0.4); letter-spacing:1.5px; margin-bottom:4px;">{{ strtoupper(__('dashboard.report.thisMonthSaved')) }}</div>
                <div style="font-size: var(--text-2xl); font-weight:800; color:#A855F7; letter-spacing:1px;">
                    ${{ number_format($stats['monthly_discount_used'], 2) }}
                </div>
            </div>
            <div style="background:rgba(249,115,22,0.08); border:1px solid rgba(249,115,22,0.2);
                 border-radius:10px; padding:14px 20px; flex:1; min-width:160px;">
                <div style="font-size: var(--title-size); color:rgba(255,255,255,0.4); letter-spacing:1.5px; margin-bottom:4px;">{{ strtoupper(__('dashboard.report.discountUses30d')) }}</div>
                <div style="font-size: var(--text-2xl); font-weight:800; color:#F97316; letter-spacing:1px;">
                    {{ number_format($stats['monthly_discount_count']) }}
                </div>
            </div>
            <div style="background:rgba(34,197,94,0.07); border:1px solid rgba(34,197,94,0.18);
                 border-radius:10px; padding:14px 20px; flex:1; min-width:160px;">
                <div style="font-size: var(--title-size); color:rgba(255,255,255,0.4); letter-spacing:1.5px; margin-bottom:4px;">{{ strtoupper(__('dashboard.stats.totalRevenue')) }}</div>
                <div style="font-size: var(--text-2xl); font-weight:800; color:#22C55E; letter-spacing:1px;">
                    ${{ number_format($stats['total_revenue'], 0) }}
                </div>
            </div>
            <div style="background:rgba(59,130,246,0.07); border:1px solid rgba(59,130,246,0.18);
                 border-radius:10px; padding:14px 20px; flex:1; min-width:160px;">
                <div style="font-size: var(--title-size); color:rgba(255,255,255,0.4); letter-spacing:1.5px; margin-bottom:4px;">{{ strtoupper(__('dashboard.stats.totalOrders')) }}</div>
                <div style="font-size: var(--text-2xl); font-weight:800; color:#3B82F6; letter-spacing:1px;">
                    {{ number_format($stats['total_orders']) }}
                </div>
            </div>
        </div>

        {{-- Export form --}}
        <form action="{{ route('dashboard.export') }}" method="GET"
              style="display:flex; align-items:flex-end; gap:14px; flex-wrap:wrap;
                     padding:20px; background:rgba(255,255,255,0.02);
                     border:1px solid rgba(255,255,255,0.07); border-radius:12px;">

            <div style="display:flex; flex-direction:column; gap:6px;">
                <label style="font-size: var(--title-size); color:rgba(255,255,255,0.4); letter-spacing:1.5px; font-weight:700;">{{ strtoupper(__('dashboard.report.fromMonth')) }}</label>
                <input type="month" name="from"
                       class="export-input"
                       value="{{ now()->subMonth()->format('Y-m') }}"
                       max="{{ now()->format('Y-m') }}" />
            </div>

            <div style="display:flex; flex-direction:column; gap:6px;">
                <label style="font-size: var(--title-size); color:rgba(255,255,255,0.4); letter-spacing:1.5px; font-weight:700;">{{ strtoupper(__('dashboard.report.toMonth')) }}</label>
                <input type="month" name="to"
                       class="export-input"
                       value="{{ now()->format('Y-m') }}"
                       max="{{ now()->format('Y-m') }}" />
            </div>

            <div style="display:flex; flex-direction:column; gap:6px;">
                <label style="font-size: var(--title-size); color:rgba(255,255,255,0.4); letter-spacing:1.5px; font-weight:700;">{{ strtoupper(__('dashboard.report.format')) }}</label>
                <select name="format" class="export-input export-select">
                    <option value="xlsx">📊 {{ __('dashboard.report.excel') }}</option>
                    <!-- <option value="csv">📄 CSV (.csv) — Summary only</option> -->
                    <option value="pdf">📕 {{ __('dashboard.report.pdf') }}</option>
                </select>
            </div>

            <button type="submit" class="btn btn-orange" style="padding:10px 24px; gap:8px;">
                ⬇ {{ strtoupper(__('dashboard.export.button')) }}
            </button>

        </form>

        {{-- Validation error --}}
        @if($errors->has('export'))
        <div style="margin-top:12px; padding:10px 16px; background:rgba(239,68,68,0.1);
             border:1px solid rgba(239,68,68,0.3); border-radius:8px;
             color:#ef4444; font-size: var(--title-size); font-weight:600;">
            ⚠ {{ $errors->first('export') }}
        </div>
        @endif

    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════════
     SECTION 2 — TOP DISCOUNT CODES THIS MONTH
══════════════════════════════════════════════════════════════════════════════ --}}
@if(isset($top_discount_codes) && $top_discount_codes->isNotEmpty())
<div class="card" style="margin-bottom:24px;">
    <div class="card-header">
            <span class="card-title">🏷️ TOP DISCOUNT CODES THIS MONTH ({{ $top_discount_codes->count() }})</span>
        <a href="{{ route('dashboard.discounts') }}" class="btn btn-outline btn-sm">VIEW ALL</a>
    </div>
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
                        <span style="font-family:monospace; font-size: var(--title-size); font-weight:700;
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

{{-- ══════════════════════════════════════════════════════════════════════════════
     SECTION 3 — CHARTS
══════════════════════════════════════════════════════════════════════════════ --}}

{{-- Row 1: Monthly Revenue + Monthly Orders --}}
<div class="chart-grid-2" style="margin-bottom:20px;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">📈 {{ strtoupper(__('dashboard.report.monthlyRevenue')) }}</span>
            <span class="chart-badge">{{ $month->format('F Y') }}</span>
        </div>
        <div class="card-body">
            <canvas id="revenueChart" height="110"></canvas>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <span class="card-title">📦 {{ strtoupper(__('dashboard.report.monthlyOrders')) }}</span>
            <span class="chart-badge">{{ $month->format('F Y') }}</span>
        </div>
        <div class="card-body">
            <canvas id="ordersChart" height="110"></canvas>
        </div>
    </div>
</div>

{{-- Row 2: Daily Sales + User Registrations --}}
<div class="chart-grid-2" style="margin-bottom:20px;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">📅 {{ strtoupper(__('dashboard.report.revenueTrend')) }}</span>
            <span class="chart-badge">{{ __('dashboard.report.last12Months') }}</span>
        </div>
        <div class="card-body">
            <canvas id="dailyChart" height="110"></canvas>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <span class="card-title">👤 {{ strtoupper(__('dashboard.report.userRegistrations')) }}</span>
            <span class="chart-badge">Last 12 Months</span>
        </div>
        <div class="card-body">
            <canvas id="usersChart" height="110"></canvas>
        </div>
    </div>
</div>

{{-- Row 3: Order Status Pie + Category Revenue Doughnut --}}
<div class="chart-grid-2" style="margin-bottom:20px;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">🥧 {{ strtoupper(__('dashboard.report.orderStatus')) }}</span>
            <span class="chart-badge">{{ __('dashboard.report.allTime') }}</span>
        </div>
        <div class="card-body" style="display:flex; justify-content:center;">
            <div style="width:260px; height:260px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <span class="card-title">🍩 {{ strtoupper(__('dashboard.report.revenueByCategory')) }}</span>
            <span class="chart-badge">{{ __('dashboard.report.allTime') }}</span>
        </div>
        <div class="card-body" style="display:flex; justify-content:center;">
            <div style="width:260px; height:260px;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════════
     SECTION 4 — TOP PRODUCTS + LOW STOCK
══════════════════════════════════════════════════════════════════════════════ --}}
<div class="chart-grid-2" style="margin-bottom:20px;">

    <div class="card">
        <div class="card-header">
            <span class="card-title">🏆 {{ strtoupper(__('dashboard.report.topProducts')) }} ({{ $top_products->count() }})</span>
            <a href="{{ route('dashboard.products') }}" class="btn btn-outline btn-sm">{{ strtoupper(__('dashboard.report.viewAll')) }}</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>{{ strtoupper(__('dashboard.table.product')) }}</th><th>{{ strtoupper(__('dashboard.table.category')) }}</th><th>{{ strtoupper(__('dashboard.table.sold')) }}</th></tr>
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
                                    <div class="product-thumb" style="display:flex; align-items:center; justify-content:center; font-size: var(--title-size);">📦</div>
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
            <span class="card-title">⚠ {{ strtoupper(__('dashboard.report.lowStock')) }} ({{ $low_stock->count() }})</span>
            <a href="{{ route('dashboard.products', ['stock'=>'low']) }}" class="btn btn-outline btn-sm">{{ strtoupper(__('dashboard.report.viewAll')) }}</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>{{ strtoupper(__('dashboard.table.product')) }}</th><th>{{ strtoupper(__('dashboard.table.stock')) }}</th></tr>
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
                                    <div class="product-thumb" style="display:flex; align-items:center; justify-content:center; font-size: var(--title-size);">📦</div>
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

@endif

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script>
// ── Period selector ───────────────────────────────────────────────────────────
// Computes the export from/to month range for a given period.
function periodRange(period) {
    const now = new Date();
    const fmtMonth = (d) => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');

    switch (period) {
        case 'today':
            return { from: fmtMonth(now), to: fmtMonth(now) };
        case '6-months': {
            const m = new Date(now); m.setMonth(now.getMonth() - 5); // inclusive range
            return { from: fmtMonth(m), to: fmtMonth(now) };
        }
        case 'this-year':
            return { from: now.getFullYear() + '-01', to: fmtMonth(now) };
        case 'about':
        default:
            return { from: '', to: '' }; // all time — empty = everything
    }
}

// Syncs the export form's from/to month pickers to the selected period (no reload).
function syncExportRange(period) {
    const { from, to } = periodRange(period);
    const fromEl = document.querySelector('input[name="from"]');
    const toEl   = document.querySelector('input[name="to"]');
    if (fromEl) fromEl.value = from;
    if (toEl)   toEl.value = to;
}

// Called on period change: sync the export range, then reload with ?period=
// so the report label reflects it.
function applyPeriod(period) {
    syncExportRange(period);
    const params = new URLSearchParams(window.location.search);
    params.set('period', period);
    if (period === 'about') params.delete('month');
    window.location.href = window.location.pathname + '?' + params.toString();
}

// On load, sync the export range to the current period WITHOUT reloading.
document.addEventListener('DOMContentLoaded', function () {
    const sel = document.getElementById('periodSelect');
    if (sel) syncExportRange(sel.value);
});
// ── Theme-aware colour helpers ────────────────────────────────────────────────
function isLight() {
    return document.documentElement.getAttribute('data-theme') === 'light';
}
function themeColors() {
    const l = isLight();
    return {
        text:      l ? 'rgba(15,23,42,0.50)'   : 'rgba(255,255,255,0.45)',
        grid:      l ? 'rgba(15,23,42,0.06)'   : 'rgba(255,255,255,0.06)',
        tooltipBg: l ? '#FFFFFF'                : '#1A1A1A',
        tooltipBdr:l ? 'rgba(249,115,22,0.35)' : 'rgba(249,115,22,0.4)',
        pieBorder: l ? '#F1F5F9'               : '#111',
        bodyClr:   l ? 'rgba(15,23,42,0.75)'  : 'rgba(255,255,255,0.8)',
    };
}
function applyChartDefaults() {
    const c = themeColors();
    Chart.defaults.color                           = c.text;
    Chart.defaults.borderColor                     = c.grid;
    Chart.defaults.font.family                     = "'Rajdhani', sans-serif";
    Chart.defaults.font.size                       = 12;
    Chart.defaults.plugins.legend.labels.boxWidth  = 12;
    Chart.defaults.plugins.legend.labels.padding   = 16;
    Chart.defaults.plugins.tooltip.backgroundColor = c.tooltipBg;
    Chart.defaults.plugins.tooltip.borderColor     = c.tooltipBdr;
    Chart.defaults.plugins.tooltip.borderWidth     = 1;
    Chart.defaults.plugins.tooltip.padding         = 10;
    Chart.defaults.plugins.tooltip.titleColor      = '#F97316';
    Chart.defaults.plugins.tooltip.bodyColor       = c.bodyClr;
}
applyChartDefaults();

// ── Register datalabels plugin globally ───────────────────────────────────
Chart.register(ChartDataLabels);
const dlBase = {
    color: isLight() ? '#0F172A' : 'rgba(255,255,255,0.9)',
    font: { weight: '600', size: 10 },
    anchor: 'end',
    align: 'end',
    offset: 1,
    clamp: true,
    clip: false,
};
const dlDollar = { ...dlBase, formatter: function(v) { return v === 0 ? '' : '$' + v.toLocaleString(); } };
const dlNumber = { ...dlBase, formatter: function(v) { return v === 0 ? '' : v.toLocaleString(); } };

// ── Data from Laravel ─────────────────────────────────────────────────────────
const monthDailyLabels   = @json($monthDailyLabels);
const monthRevenueDaily  = @json($monthRevenueDaily);
const monthOrdersDaily   = @json($monthOrdersDaily);
const monthlySalesLabels = @json($monthlySalesLabels);
const monthlySalesRevenue= @json($monthlySalesRevenue);
const monthlyUsers       = @json($monthlyUserCounts);
const dailyLabels        = @json($dailyLabels);
const dailyRevenue       = @json($dailyRevenue);
const statusLabels       = @json($statusLabels);
const statusCounts       = @json($statusCounts);
const categoryLabels     = @json($categoryLabels);
const categoryRevData    = @json($categoryRevData);

// ── Palette ───────────────────────────────────────────────────────────────────
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

// 1. Daily Revenue for selected month — Line
const rCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(rCtx, {
    type: 'line',
    data: { labels: monthDailyLabels, datasets: [{ label:'Revenue ($)', data:monthRevenueDaily,
        borderColor:orange, borderWidth:2.5, pointBackgroundColor:orange,
        pointBorderColor: isLight() ? '#F1F5F9' : '#111', pointBorderWidth:2, pointRadius:4, pointHoverRadius:7,
        fill:true, backgroundColor:makeGradient(rCtx,'rgba(249,115,22,0.25)','rgba(249,115,22,0)'), tension:0.4 }] },
    options: { responsive:true, layout:{ padding:{ top: 24 } }, plugins:{ legend:{display:false}, datalabels:dlDollar,
        tooltip:{ callbacks:{ label: c => ' $'+c.parsed.y.toLocaleString() }}},
        scales:{ x:{grid:{color: themeColors().grid}}, y:{grid:{color: themeColors().grid},
            ticks:{ callback: v => '$'+v.toLocaleString() }}}}
});

// 2. Daily Orders for selected month — Bar
const oCtx = document.getElementById('ordersChart').getContext('2d');
const ordersChart = new Chart(oCtx, {
    type: 'bar',
    data: { labels: monthDailyLabels, datasets: [{ label:'Orders', data:monthOrdersDaily,
        backgroundColor:makeGradient(oCtx, orangeMid,'rgba(249,115,22,0.15)'),
        borderColor:orange, borderWidth:1.5, borderRadius:6, borderSkipped:false }] },
    options: { responsive:true, layout:{ padding:{ top: 24 } }, plugins:{legend:{display:false}, datalabels:dlNumber},
        scales:{ x:{grid:{color: themeColors().grid}}, y:{grid:{color: themeColors().grid},
            ticks:{stepSize:1}}}}
});

// 3. Monthly Revenue (12 months) — Line
const dCtx = document.getElementById('dailyChart').getContext('2d');
const dailyChart = new Chart(dCtx, {
    type: 'line',
    data: { labels: monthlySalesLabels, datasets: [{ label:'Revenue ($)', data:monthlySalesRevenue,
        borderColor:blue, borderWidth:2.5, pointBackgroundColor:blue,
        pointBorderColor: isLight() ? '#F1F5F9' : '#111', pointBorderWidth:2, pointRadius:4, pointHoverRadius:7,
        fill:true, backgroundColor:makeGradient(dCtx,'rgba(59,130,246,0.25)','rgba(59,130,246,0)'), tension:0.4 }] },
    options: { responsive:true, layout:{ padding:{ top: 24 } }, plugins:{ legend:{display:false}, datalabels:dlDollar,
        tooltip:{ callbacks:{ label: c => ' $'+c.parsed.y.toLocaleString() }}},
        scales:{ x:{grid:{color: themeColors().grid}}, y:{grid:{color: themeColors().grid},
            ticks:{ callback: v => '$'+v.toLocaleString() }}}}
});

// 4. User Registrations (12 months) — Bar
const uCtx = document.getElementById('usersChart').getContext('2d');
const usersChart = new Chart(uCtx, {
    type: 'bar',
    data: { labels: monthlySalesLabels, datasets: [{ label:'New Users', data:monthlyUsers,
        backgroundColor:makeGradient(uCtx,'rgba(34,197,94,0.6)','rgba(34,197,94,0.1)'),
        borderColor:green, borderWidth:1.5, borderRadius:6, borderSkipped:false }] },
    options: { responsive:true, layout:{ padding:{ top: 24 } }, plugins:{legend:{display:false}, datalabels:dlNumber},
        scales:{ x:{grid:{color: themeColors().grid}}, y:{grid:{color: themeColors().grid},
            ticks:{stepSize:1}}}}
});

// 5. Order Status — Pie
const statusChart = new Chart(document.getElementById('statusChart').getContext('2d'), {
    type: 'pie',
    data: { labels: statusLabels.map(s => s.toUpperCase()),
        datasets:[{ data:statusCounts, backgroundColor:[yellow,green,blue,purple,red],
            borderColor: isLight() ? '#F1F5F9' : '#111', borderWidth:3, hoverOffset:8 }] },
    options: { responsive:true, plugins:{ legend:{position:'bottom',labels:{padding:14,font:{size:11}}},
        tooltip:{ callbacks:{ label: c => ' '+c.label+': '+c.parsed+' orders' }}}}
});

// 6. Revenue by Category — Doughnut
const categoryChart = new Chart(document.getElementById('categoryChart').getContext('2d'), {
    type: 'doughnut',
    data: { labels: categoryLabels,
        datasets:[{ data:categoryRevData, backgroundColor:pieColors,
            borderColor: isLight() ? '#F1F5F9' : '#111', borderWidth:3, hoverOffset:8 }] },
    options: { responsive:true, cutout:'60%', plugins:{ legend:{position:'bottom',labels:{padding:14,font:{size:11}}},
        tooltip:{ callbacks:{ label: c => ' '+c.label+': $'+c.parsed.toLocaleString() }}}}
});

// ── Live Chart theme updater ──────────────────────────────────────────────────
window.__updateChartTheme = function(t) {
    applyChartDefaults();
    const c  = themeColors();
    const bd = t === 'light' ? '#F1F5F9' : '#111';
    [revenueChart, ordersChart, dailyChart, usersChart].forEach(ch => {
        ch.options.scales.x.grid.color = c.grid;
        ch.options.scales.y.grid.color = c.grid;
        ch.update('none');
    });
    [statusChart, categoryChart].forEach(ch => {
        ch.data.datasets[0].borderColor = bd;
        ch.update('none');
    });
    [revenueChart, dailyChart].forEach(ch => {
        ch.data.datasets[0].pointBorderColor = bd;
        ch.update('none');
    });
};
</script>
@endpush

@push('styles')
<style>
.chart-badge {
    font-size: var(--title-size);
    color: rgba(255,255,255,0.35);
    background: rgba(255,255,255,0.05);
    padding: 3px 10px;
    border-radius: 20px;
    letter-spacing: 1px;
}

/* Export form inputs */
.export-input {
    background: var(--dark-700, #1A1A1A);
    border: 1px solid rgba(255,255,255,0.12);
    color: #fff;
    border-radius: 8px;
    padding: 9px 13px;
    font-size: var(--title-size);
    font-family: Rajdhani, sans-serif;
    font-weight: 500;
    transition: border-color .2s;
    outline: none;
}
.export-input:focus { border-color: #F97316; }
.export-select { cursor: pointer; padding-right: 32px; }

/* Light mode overrides */
[data-theme="light"] .export-input {
    background: #F8FAFC !important;
    border-color: rgba(15,23,42,0.14) !important;
    color: #0F172A !important;
}
[data-theme="light"] label[style*="color:rgba(255,255,255,0.4)"] {
    color: rgba(15,23,42,0.45) !important;
}
[data-theme="light"] [style*="background:rgba(168,85,247,0.08)"] {
    background: rgba(168,85,247,0.07) !important;
}
[data-theme="light"] [style*="background:rgba(249,115,22,0.08)"] {
    background: rgba(249,115,22,0.06) !important;
}
[data-theme="light"] td[style*="color:rgba(255,255,255,0.3)"] {
    color: rgba(15,23,42,0.35) !important;
}

@media (max-width: 900px) {
    .chart-grid-2 { grid-template-columns: 1fr; }
}
</style>
@endpush
