@extends('dashboard.layout')
@section('title', strtoupper(__('dashboard.nav.dashboard')))

@section('content')

@include('dashboard._permission_check', ['feature' => 'dashboard'])
@php $_permDenied = $GLOBALS['_tronmatix_perm_denied'] ?? false; @endphp
@if(!$_permDenied)

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
            <div class="stat-value" id="stat-total-users" style="overflow:hidden; text-overflow:ellipsis;">{{ number_format($stats['total_users']) }}</div>
            <div class="stat-label">{{ __('dashboard.stats.totalUsers') }}
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
            </svg>
        </div>
        <div>
            <div class="stat-value" id="stat-total-products" style="overflow:hidden; text-overflow:ellipsis;">{{ number_format($stats['total_products']) }}</div>
            <div class="stat-label">{{ __('dashboard.stats.totalProducts') }}</div>
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
            <div class="stat-value" id="stat-total-orders" style="overflow:hidden; text-overflow:ellipsis;">{{ number_format($stats['total_orders']) }}</div>
            <div class="stat-label">{{ __('dashboard.stats.totalOrders') }}
            </div>
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
            <div class="stat-value" id="stat-total-revenue">${{ number_format($stats['total_revenue'], 0) }}</div>
            <div class="stat-label">{{ __('dashboard.stats.totalRevenue') }}
            </div>
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
            <div class="stat-label">{{ __('dashboard.stats.pendingOrders') }}</div>
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
            <div class="stat-label">{{ __('dashboard.stats.activeItems') }}</div>
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
            <div class="stat-value" style="color:#A855F7;">${{ number_format($stats['total_discount_used']) }}</div>
            <div class="stat-label">{{ __('dashboard.stats.discountsSaved') }}</div>
        </div>
    </div>
</div>

{{-- ── Row 1: Monthly Revenue + Monthly Orders ──────────────────────────────── --}}
<div class="chart-grid-2" style="margin-bottom:20px;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">📈{{ __('dashboard.charts.monthlyRevenue') }}</span>
            <span class="chart-badge">{{ __('dashboard.stats.last12Months') }}</span>
        </div>
        <div class="card-body">
            <canvas id="revenueChart" height="110"></canvas>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <span class="card-title">📦{{ __('dashboard.charts.monthlyOrders') }}</span>
            <span class="chart-badge">{{ __('dashboard.stats.last12Months') }}</span>
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
            <span class="card-title">📅{{ __('dashboard.charts.dailySales') }}</span>
            <span class="chart-badge">{{ __('dashboard.stats.last14Days') }}</span>
        </div>
        <div class="card-body">
            <canvas id="dailyChart" height="110"></canvas>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <span class="card-title">👤{{ __('dashboard.charts.userRegistrations') }}</span>
            <span class="chart-badge">{{ __('dashboard.stats.last12Months') }}</span>
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
            <span class="card-title">🥧{{ __('dashboard.charts.orderStatus') }}</span>
            <span class="chart-badge">{{ __('dashboard.stats.allTime') }}</span>
        </div>
        <div class="card-body" style="display:flex; justify-content:center;">
            <div style="width:260px; height:260px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <span class="card-title">🍩{{ __('dashboard.charts.revenueByCategory') }}</span>
            <span class="chart-badge">{{ __('dashboard.stats.allTime') }}</span>
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
            <span class="card-title">🕒{{ __('dashboard.charts.recentOrders') }}</span>
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
                               style="color:#F97316; font-weight:700; font-family:monospace; font-size: var(--title-size); text-decoration:none;">
                                {{ $order->order_id }}
                            </a>
                        </td>
                        <td style="font-weight:600;">{{ $order->user?->username ?? 'Guest' }}</td>
                        <td style="color:#F97316; font-weight:700;">${{ number_format($order->total, 2) }}</td>
                        <td><span class="badge badge-{{ $order->status }}">{{ strtoupper($order->status) }}</span></td>
                        <td style="color:var(--date-cell-color, rgba(255,255,255,0.4)); font-size: var(--title-size);">{{ $order->created_at->format('d M Y') }}</td>
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
                <span class="card-title">🏆{{ __('dashboard.charts.topProducts') }}</span>
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
                <span class="card-title">⚠ {{ __('dashboard.charts.lowStock') }}</span>
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
</div>

@endif
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
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
    const isMobile = window.innerWidth < 768;
    Chart.defaults.color                           = c.text;
    Chart.defaults.borderColor                     = c.grid;
    Chart.defaults.font.family                     = "'Rajdhani', sans-serif";
    Chart.defaults.font.size                       = isMobile ? 10 : 12;
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
const revenueChart = new Chart(rCtx, {
    type: 'line',
    data: { labels: monthlyLabels, datasets: [{ label:'Revenue ($)', data:monthlyRevenue,
        borderColor:orange, borderWidth:2.5, pointBackgroundColor:orange,
        pointBorderColor: isLight() ? '#F1F5F9' : '#111', pointBorderWidth:2, pointRadius:4, pointHoverRadius:7,
        fill:true, backgroundColor:makeGradient(rCtx,'rgba(249,115,22,0.25)','rgba(249,115,22,0)'), tension:0.4 }] },
    options: { responsive:true, plugins:{ legend:{display:false},
        tooltip:{ callbacks:{ label: c => ' $'+c.parsed.y.toLocaleString() }}},
        scales:{ x:{grid:{color: themeColors().grid}, ticks:{ maxTicksLimit:6, maxRotation:45, font:{size: window.innerWidth < 768 ? 9 : 12} }}, y:{grid:{color: themeColors().grid},
            ticks:{ callback: v => '$'+v.toLocaleString() }}}}
});

// 2. Monthly Orders — Bar
const oCtx = document.getElementById('ordersChart').getContext('2d');
const ordersChart = new Chart(oCtx, {
    type: 'bar',
    data: { labels: monthlyLabels, datasets: [{ label:'Orders', data:monthlyOrders,
        backgroundColor:makeGradient(oCtx, orangeMid,'rgba(249,115,22,0.15)'),
        borderColor:orange, borderWidth:1.5, borderRadius:6, borderSkipped:false }] },
    options: { responsive:true, plugins:{legend:{display:false}},
        scales:{ x:{grid:{color: themeColors().grid}, ticks:{ maxTicksLimit:6, maxRotation:45, font:{size: window.innerWidth < 768 ? 9 : 12} }}, y:{grid:{color: themeColors().grid},
            ticks:{stepSize:1}}}}
});

// 3. Daily Sales — Line
const dCtx = document.getElementById('dailyChart').getContext('2d');
const dailyChart = new Chart(dCtx, {
    type: 'line',
    data: { labels: dailyLabels, datasets: [{ label:'Revenue ($)', data:dailyRevenue,
        borderColor:blue, borderWidth:2.5, pointBackgroundColor:blue,
        pointBorderColor: isLight() ? '#F1F5F9' : '#111', pointBorderWidth:2, pointRadius:4, pointHoverRadius:7,
        fill:true, backgroundColor:makeGradient(dCtx,'rgba(59,130,246,0.25)','rgba(59,130,246,0)'), tension:0.4 }] },
    options: { responsive:true, plugins:{ legend:{display:false},
        tooltip:{ callbacks:{ label: c => ' $'+c.parsed.y.toLocaleString() }}},
        scales:{ x:{grid:{color: themeColors().grid}, ticks:{ maxTicksLimit:6, maxRotation:45, font:{size: window.innerWidth < 768 ? 9 : 12} }}, y:{grid:{color: themeColors().grid},
            ticks:{ callback: v => '$'+v.toLocaleString() }}}}
});

// 4. User Registrations — Bar
const uCtx = document.getElementById('usersChart').getContext('2d');
const usersChart = new Chart(uCtx, {
    type: 'bar',
    data: { labels: monthlyLabels, datasets: [{ label:'New Users', data:monthlyUsers,
        backgroundColor:makeGradient(uCtx,'rgba(34,197,94,0.6)','rgba(34,197,94,0.1)'),
        borderColor:green, borderWidth:1.5, borderRadius:6, borderSkipped:false }] },
    options: { responsive:true, plugins:{legend:{display:false}},
        scales:{ x:{grid:{color: themeColors().grid}, ticks:{ maxTicksLimit:6, maxRotation:45, font:{size: window.innerWidth < 768 ? 9 : 12} }}, y:{grid:{color: themeColors().grid},
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

// ── Live Chart theme updater (called by layout toggleTheme) ───────────────────
window.__updateChartTheme = function(t) {
    applyChartDefaults();
    const c  = themeColors();
    const bd = t === 'light' ? '#FFFFFF' : '#111';
    Chart.defaults.elements.point.borderColor = bd;
    // Update grid + tick colors on all 4 axis charts
    [revenueChart, ordersChart, dailyChart, usersChart].forEach(ch => {
        ch.options.scales.x.grid.color  = c.grid;
        ch.options.scales.y.grid.color  = c.grid;
        ch.options.scales.x.ticks.color = c.text;
        ch.options.scales.y.ticks.color = c.text;
        ch.update('none');
    });

    // Regenerate gradients for line charts
    revenueChart.data.datasets[0].backgroundColor = makeGradient(
        revenueChart.ctx, 'rgba(249,115,22,0.25)', 'rgba(249,115,22,0)'
    );
    revenueChart.data.datasets[0].pointBorderColor = bd;
    revenueChart.data.datasets[0].pointBackgroundColor = orange;
    revenueChart.update('none');

    dailyChart.data.datasets[0].backgroundColor = makeGradient(
        dailyChart.ctx, 'rgba(59,130,246,0.25)', 'rgba(59,130,246,0)'
    );
    dailyChart.data.datasets[0].pointBorderColor = bd;
    dailyChart.data.datasets[0].pointBackgroundColor = blue;
    dailyChart.update('none');
    // Regenerate gradients for bar charts
    ordersChart.data.datasets[0].backgroundColor = makeGradient(
        ordersChart.ctx, 'rgba(249,115,22,0.6)', 'rgba(249,115,22,0.15)'
    );
    ordersChart.update('none');

    usersChart.data.datasets[0].backgroundColor = makeGradient(
        usersChart.ctx, 'rgba(34,197,94,0.6)', 'rgba(34,197,94,0.1)'
    );
    usersChart.update('none');

    // Pie/doughnut border color
    [statusChart, categoryChart].forEach(ch => {
        ch.data.datasets[0].borderColor = bd;
        ch.update('none');
    });
};

// ── Polling for Trend Updates ──────────────────────────────────────────────
setInterval(async () => {
    try {
        const response = await fetch('{{ route('dashboard.stats') }}');
        const data = await response.json();
        // Update values
        document.getElementById('stat-total-users').innerText = data.total_users.toLocaleString();
        document.getElementById('stat-total-products').innerText = data.total_products.toLocaleString();
        document.getElementById('stat-total-orders').innerText = data.total_orders.toLocaleString();
        document.getElementById('stat-total-revenue').innerText = '$' + data.total_revenue.toLocaleString();

    } catch (e) { console.error('Failed to update stats:', e); }
}, 30000); // 30 seconds
</script>
@endpush

@push('styles')
<style>
[data-theme="light"] .chart-badge {
    color: rgba(15,23,42,0.45);
    background: rgba(15,23,42,0.06);
}
[data-theme="light"] td[style*="color:rgba(255,255,255,0.4)"] {
    color: rgba(15,23,42,0.45) !important;
}
[data-theme="light"] .stat-card .stat-label {
    color: rgba(15,23,42,0.55) !important;
}

/* ── index page responsive ───────────────────────────────────────────────── */
@media (max-width: 900px) {
    .chart-grid-2 { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
    /* ── Stats: always 2 columns on mobile ── */
    .stats-grid {
        grid-template-columns: 1fr 1fr !important;
        gap: 10px !important;
    }
    .stat-card {
        padding: 14px 12px !important;
        gap: 10px !important;
        min-width: 0;
    }
    .stat-value {
        font-size: clamp(1.2rem, 4vw, 1.6rem) !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .stat-label {
        font-size: 13px !important;
        line-height: 1.3;
    }
    .stat-icon {
        width: 36px !important;
        height: 36px !important;
        flex-shrink: 0;
    }

    /* ── Charts: reduce x-axis label size to prevent overlap ── */
    .card-body canvas { max-height: 180px; }

    /* ── Recent orders table: min-width so ORDER ID doesn't clip ── */
    .table-wrap table { min-width: 480px !important; }

    /* ── Row 4 right column: stack top products + low stock ── */
    .chart-grid-2 > div[style*="flex-direction:column"] {
        min-width: 0;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr 1fr !important;
        gap: 8px !important;
    }
    .stat-card { padding: 12px 10px !important; gap: 8px !important; }
    .stat-value { font-size: clamp(1.1rem, 3.5vw, 1.4rem) !important; }
    .stat-icon { width: 32px !important; height: 32px !important; }
    .stat-icon svg { width: 16px !important; height: 16px !important; }
}
</style>
@endpush
