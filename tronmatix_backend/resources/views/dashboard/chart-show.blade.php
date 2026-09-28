@extends('dashboard.layout')
@section('title', strtoupper($chartTitle))

@section('content')

@include('dashboard._permission_check', ['feature' => 'dashboard'])
@php $_permDenied = $GLOBALS['_tronmatix_perm_denied'] ?? false; @endphp
@if(!$_permDenied)

{{-- ── Page Header ─────────────────────────────────────────────────────────────── ── --}}
<div style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap;
            gap:16px; margin-bottom:20px;">
    <div style="display:flex; align-items:center; gap:14px;">
        <a href="{{ route('dashboard.index') }}"
           style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;
                  width:44px; height:44px; border-radius:14px; background:var(--hover-bg);
                  border:1px solid var(--border-input); color:var(--text-muted); font-size: var(--title-size);
                  transition:all .2s;"
           onmouseover="this.style.borderColor='#F97316'; this.style.color='#F97316'"
           onmouseout="this.style.borderColor=''; this.style.color=''"
           title="← {{ strtoupper(__('dashboard.nav.dashboard')) }}">←</a>
        <div>
            <div style="font-size: var(--title-size); font-weight:900; letter-spacing:3px;">
                {{ strtoupper($chartTitle) }}
            </div>
            <div style="font-size: var(--title-size); color:var(--text-muted); margin-top:2px;">
                {{ strtoupper($rangeNames[$range] ?? $range) }}
            </div>
        </div>
    </div>
</div>

{{-- ── Range Tabs (plain <a> links, full navigation like the period select) ── --}}
<div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:20px;">
    <div style="font-size: var(--title-size); font-weight:700; color:var(--text-muted); letter-spacing:1px; text-transform:uppercase;">
        {{ strtoupper(__('dashboard.revenue.range')) }}:
    </div>
    @php
        $rangeTabs = [
            'today'     => ['label' => 'TODAY',      'icon' => '🗓️'],
            'month'     => ['label' => 'MONTH',      'icon' => '📆'],
            '6-months'  => ['label' => '6 MONTHS',   'icon' => '🗓️'],
            'this-year' => ['label' => 'YEAR',       'icon' => '📅'],
        ];
    @endphp
    @foreach ($rangeTabs as $key => $tab)
        @php $isActive = ($range ?? 'month') === $key; @endphp
        <a href="{{ route('dashboard.charts.show', ['chart' => $chart, 'range' => $key]) }}"
           style="display:inline-flex; align-items:center; gap:6px; padding:7px 16px; border-radius:30px;
                  font-family:Rajdhani, var(--font-kh), sans-serif; font-size: var(--text-sm); font-weight:700;
                  letter-spacing:1px; text-decoration:none; transition:all 0.2s;
                  background: {{ $isActive ? '#F97316' : 'var(--hover-bg)' }};
                  color:      {{ $isActive ? '#fff' : 'var(--text-muted)' }};
                  border: 1.5px solid {{ $isActive ? '#F97316' : 'var(--border-input)' }};
                  box-shadow: {{ $isActive ? '0 0 12px rgba(249,115,22,.4)' : 'none' }};"
           onmouseover="this.style.opacity='.82'" onmouseout="this.style.opacity='1'">
            {{ $tab['icon'] }} {{ $tab['label'] }}
        </a>
    @endforeach
</div>

{{-- ── Chart — one full-width card, single large canvas ─────────────────────── --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <span class="card-title">📊 {{ $chartTitle }}</span>
        <span class="chart-badge">{{ strtoupper($rangeNames[$range] ?? $range) }}</span>
    </div>
    <div class="card-body">
        @php $isPie = in_array($chart, ['status', 'category']); @endphp
        <div style="{{ $isPie ? 'max-width:420px; margin:0 auto;' : 'position:relative;' }}">
            <canvas id="detailChart" height="50"></canvas>
        </div>
    </div>
</div>

@if($chart === 'users')
{{-- ── Recently Logged In users (replaces Recent Orders on the Users page) ── --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <span class="card-title">🕒 {{ __('dashboard.charts.recentLogins') }}</span>
        <a href="{{ route('dashboard.users', ['recent' => 1]) }}" class="btn btn-outline btn-sm">VIEW ALL</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>USER</th>
                    <th>EMAIL</th>
                    <th>ROLE</th>
                    <th>ORDERS</th>
                    <th>LAST LOGIN</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recent_users as $user)
                <tr>
                    <td style="font-weight:600;">
                        {{ $user->username }}
                        @if(($user->role ?? 'customer') === 'vip')
                            <span style="font-size:10px; font-weight:800; color:#fff; background:#F97316; padding:1px 6px; border-radius:3px; white-space:nowrap; line-height:1.2; margin-left:4px;">⭐ VIP</span>
                        @endif
                    </td>
                    <td style="color:var(--text-muted);">{{ $user->email ?? '—' }}</td>
                    <td><span class="badge badge-{{ in_array($user->role, ['vip','reseller']) ? 'paid' : ($user->role === 'banned' ? 'cancelled' : 'gray') }}">{{ strtoupper(\App\Models\User::ROLE_LABELS[$user->role ?? 'customer'] ?? 'Customer') }}</span></td>
                    <td style="color:#F97316; font-weight:700;">{{ $user->orders_count }}</td>
                    <td style="color:var(--date-cell-color, rgba(255,255,255,0.4)); font-size: var(--title-size); white-space:nowrap;">{{ $user->last_login_at->format('d M Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; color:rgba(255,255,255,0.3); padding:30px;">No users logged in during this period</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:14px 20px; border-top:1px solid var(--border);">
        {{ $recent_users->links('dashboard.pagination') }}
    </div>
</div>
@elseif($chart === 'status')
{{-- ── Order Status breakdown (matches the Order Status pie chart) ────────── --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <span class="card-title">📊 {{ $chartTitle }}</span>
        <a href="{{ route('dashboard.orders') }}" class="btn btn-outline btn-sm">VIEW ALL</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>STATUS</th>
                    <th style="text-align:right;">ORDERS</th>
                    <th style="text-align:right;">%</th>
                </tr>
            </thead>
            <tbody>
                @php $statusTotal = array_sum($values); @endphp
                @foreach($labels as $i => $label)
                    @php $count = $values[$i] ?? 0; $statusPct = $statusTotal > 0 ? round(($count / $statusTotal) * 100) : 0; @endphp
                    <tr>
                        <td><span class="badge badge-{{ strtolower($label) }}">{{ $label }}</span></td>
                        <td style="color:#F97316; font-weight:700; text-align:right;">{{ number_format($count) }}</td>
                        <td style="text-align:right; color:var(--text-muted);">{{ $statusPct }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@elseif($chart === 'category')
{{-- ── Revenue by Category breakdown (matches the doughnut chart) ────────── --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <span class="card-title">📊 {{ $chartTitle }}</span>
        <a href="{{ route('dashboard.products') }}" class="btn btn-outline btn-sm">VIEW ALL</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>CATEGORY</th>
                    <th style="text-align:right;">REVENUE</th>
                    <th style="text-align:right;">%</th>
                </tr>
            </thead>
            <tbody>
                @php $categoryTotal = array_sum($values); @endphp
                @foreach($labels as $i => $label)
                    @php $rev = $values[$i] ?? 0; $catPct = $categoryTotal > 0 ? round(($rev / $categoryTotal) * 100) : 0; @endphp
                    <tr>
                        <td><span class="badge badge-orange">{{ $label }}</span></td>
                        <td style="color:#F97316; font-weight:700; text-align:right;">${{ number_format($rev, 2) }}</td>
                        <td style="text-align:right; color:var(--text-muted);">{{ $catPct }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
{{-- ── Recent Orders ────────────────────────────────────────────────────────── --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <span class="card-title">🕒{{ __('dashboard.charts.recentOrders') }}</span>
        <a href="{{ route('dashboard.orders', ['month' => now()->format('Y-m')]) }}" class="btn btn-outline btn-sm">VIEW ALL</a>
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
                        <a href="{{ route('dashboard.orders.show', $order->order_id) }}"
                           style="color:#F97316; font-weight:700; font-family:monospace; font-size: var(--title-size); text-decoration:none;">
                            {{ $order->order_id }}
                        </a>
                    </td>
                    <td style="font-weight:600;">
                        {{ $order->user?->username ?? 'Guest' }}
                        @if(($order->user?->role ?? '') === 'vip')
                            <span style="font-size:10px; font-weight:800; color:#fff; background:#F97316; padding:1px 6px; border-radius:3px; white-space:nowrap; line-height:1.2; margin-left:4px;">⭐ VIP</span>
                        @endif
                    </td>
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
    <div style="padding:14px 20px; border-top:1px solid var(--border);">
        {{ $recent_orders->links('dashboard.pagination') }}
    </div>
</div>
@endif

@endif
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

// ── Theme-aware colour helpers (verbatim from dashboard index) ───────────────
function isLight() {
    return document.documentElement.getAttribute('data-theme') === 'light';
}
function themeColors() {
    const l = isLight();
    return {
        text:      l ? 'rgba(15,23,42,0.65)'   : 'rgba(255,255,255,0.45)',
        grid:      l ? 'rgba(15,23,42,0.08)'   : 'rgba(255,255,255,0.06)',
        tooltipBg: l ? '#FFFFFF'                : '#1A1A1A',
        tooltipBdr:l ? 'rgba(249,115,22,0.35)' : 'rgba(249,115,22,0.4)',
        pieBorder: l ? '#FFFFFF'               : '#111',
        bodyClr:   l ? 'rgba(15,23,42,0.80)'  : 'rgba(255,255,255,0.8)',
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

// ── Register datalabels plugin globally ───────────────────────────────────
Chart.register(ChartDataLabels);
const dlBase = {
    color: isLight() ? '#0F172A' : 'rgba(255,255,255,0.9)',
    font: { weight: '600', size: window.innerWidth < 768 ? 12 : 12 },
    anchor: 'end',
    align: 'end',
    offset: 1,
    clamp: true,
    clip: false,
};
const dlDollar = { ...dlBase, formatter: v => v === 0 ? '' : '$' + v.toLocaleString() };
const dlNumber = { ...dlBase, formatter: v => v === 0 ? '' : v.toLocaleString() };

// ── Data from Laravel ──────────────────────────────────────────────────────────
const chart = @json($chart);
const labels = @json($labels);
const values = @json($values);

// ── Palette ────────────────────────────────────────────────────────────────────
const orange    = '#F97316';
const blue      = '#3B82F6';
const green     = '#22C55E';
const yellow    = '#EAB308';
const red       = '#EF4444';
const purple    = '#A855F7';
const pieColors = [yellow, green, blue, purple, red, orange];

function makeGradient(ctx, top, bottom) {
    const h = ctx.canvas.clientHeight || ctx.canvas.height || 300;
    const g = ctx.createLinearGradient(0, 0, 0, h);
    g.addColorStop(0, top);
    g.addColorStop(1, bottom);
    return g;
}

// Guard: skip chart init if the canvas doesn't exist (e.g. permission denied)
if (!document.getElementById('detailChart')) { return; }

let detailChart = null;
const detailCtx = document.getElementById('detailChart');

// Build ONLY the chart config that matches the selected chart — each block is
// the exact config for that canvas id in dashboard index, retargeted to
// #detailChart and the range-scoped labels/values above.
if (chart === 'revenue') {
    const rCtx = detailCtx.getContext('2d');
    const revenueGradient = makeGradient(rCtx, isLight() ? 'rgba(249,115,22,0.55)' : 'rgba(249,115,22,0.5)', isLight() ? 'rgba(249,115,22,0.06)' : 'rgba(249,115,22,0.1)');
    detailChart = new Chart(rCtx, {
        type: 'line',
        data: { labels: labels, datasets: [{ label:'Revenue ($)', data:values,
            backgroundColor:revenueGradient, fill:true,
            borderColor:orange, borderWidth:2, borderRadius:6, borderSkipped:false }] },
        options: { responsive:true, maintainAspectRatio:false, layout:{ padding:{ top: 5 } }, plugins:{ legend:{display:false}, datalabels:dlDollar,
            tooltip:{ callbacks:{ label: c => ' $'+c.parsed.y.toLocaleString() }}},
            scales:{ x:{grid:{color: themeColors().grid}, ticks:{ maxTicksLimit:8, maxRotation:45, font:{size: window.innerWidth < 768 ? 10 : 12} }}, y:{grid:{color: themeColors().grid},
                ticks:{ callback: v => '$'+v.toLocaleString(), font:{size:11}}}}}
    });
} else if (chart === 'orders') {
    const oCtx = detailCtx.getContext('2d');
    detailChart = new Chart(oCtx, {
        type: 'bar',
        data: { labels: labels, datasets: [{ label:'Orders', data:values,
            backgroundColor:makeGradient(oCtx, isLight() ? 'rgba(249,115,22,0.68)' : 'rgba(249,115,22,0.6)', isLight() ? 'rgba(249,115,22,0.10)' : 'rgba(249,115,22,0.1)'), fill:true,
            borderColor:orange, borderWidth:2, borderRadius:6, borderSkipped:false }] },
        options: { responsive:true, maintainAspectRatio:false, layout:{ padding:{ top: 24 } }, plugins:{legend:{display:false}, datalabels:dlNumber},
            scales:{ x:{grid:{color: themeColors().grid}, ticks:{ maxTicksLimit:8, maxRotation:45, font:{size: window.innerWidth < 768 ? 10 : 12} }}, y:{grid:{color: themeColors().grid},
                ticks:{stepSize:1, font:{size:11}}}}}
    });
} else if (chart === 'sales') {
    const dCtx = detailCtx.getContext('2d');
    detailChart = new Chart(dCtx, {
        type: 'line',
        data: { labels: labels, datasets: [{ label:'Revenue ($)', data:values,
            borderColor:blue, borderWidth:3, pointBackgroundColor: isLight() ? blue : '#fff',
            pointBorderColor:blue, pointBorderWidth:3, pointRadius:5, pointHoverRadius:9,
            fill:true, backgroundColor:makeGradient(dCtx, isLight() ? 'rgba(59,130,246,0.36)' : 'rgba(59,130,246,0.3)', isLight() ? 'rgba(59,130,246,0.04)' : 'rgba(59,130,246,0.01)'), tension:0.35 }] },
        options: { responsive:true, maintainAspectRatio:false, layout:{ padding:{ top: 24 } }, plugins:{ legend:{display:false}, datalabels:dlDollar,
            tooltip:{ callbacks:{ label: c => ' $'+c.parsed.y.toLocaleString() }}},
            scales:{ x:{grid:{color: themeColors().grid}, ticks:{ maxTicksLimit:6, maxRotation:45, font:{size: window.innerWidth < 768 ? 9 : 12} }}, y:{grid:{color: themeColors().grid},
                ticks:{ callback: v => '$'+v.toLocaleString() }}}}
    });
} else if (chart === 'users') {
    const uCtx = detailCtx.getContext('2d');
    detailChart = new Chart(uCtx, {
        type: 'bar',
        data: { labels: labels, datasets: [{ label:'New Users', data:values,
            backgroundColor:makeGradient(uCtx, isLight() ? 'rgba(34,197,94,0.68)' : 'rgba(34,197,94,0.6)', isLight() ? 'rgba(34,197,94,0.10)' : 'rgba(34,197,94,0.1)'),
            borderColor:green, borderWidth:1.5, borderRadius:6, borderSkipped:false }] },
        options: { responsive:true, maintainAspectRatio:false, layout:{ padding:{ top: 24 } }, plugins:{legend:{display:false}, datalabels:dlNumber},
            scales:{ x:{grid:{color: themeColors().grid}, ticks:{ maxTicksLimit:6, maxRotation:45, font:{size: window.innerWidth < 768 ? 9 : 12} }}, y:{grid:{color: themeColors().grid},
                ticks:{stepSize:1}}}}
    });
} else if (chart === 'status') {
    detailChart = new Chart(detailCtx.getContext('2d'), {
        type: 'pie',
        data: { labels: labels,
            datasets:[{ data:values, backgroundColor:[yellow,green,blue,purple,red],
                borderColor: isLight() ? '#FFFFFF' : '#111', borderWidth:3, hoverOffset:8 }] },
        options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{position:'bottom',labels:{padding:14,font:{size:11}}},
            tooltip:{ callbacks:{ label: c => ' '+c.label+': '+c.parsed+' orders' }}}}
    });
} else if (chart === 'category') {
    detailChart = new Chart(detailCtx.getContext('2d'), {
        type: 'doughnut',
        data: { labels: labels,
            datasets:[{ data:values, backgroundColor:pieColors,
                borderColor: isLight() ? '#FFFFFF' : '#111', borderWidth:3, hoverOffset:8 }] },
        options: { responsive:true, maintainAspectRatio:false, cutout:'60%', plugins:{ legend:{position:'bottom',labels:{padding:14,font:{size:11}}},
            tooltip:{ callbacks:{ label: c => ' '+c.label+': $'+c.parsed.toLocaleString() }}}}
    });
}

// ── Live Chart theme updater (called by layout toggleTheme) ───────────────────
window.__updateChartTheme = function(t) {
    if (!detailChart) return;
    applyChartDefaults();
    const c  = themeColors();
    const bd = t === 'light' ? '#FFFFFF' : '#111';
    const type = detailChart.config.type;

    if (type === 'line' || type === 'bar') {
        detailChart.options.scales.x.grid.color  = c.grid;
        detailChart.options.scales.y.grid.color  = c.grid;
        detailChart.options.scales.x.ticks.color = c.text;
        detailChart.options.scales.y.ticks.color = c.text;
        // Regenerate gradient for the single visible chart
        detailChart.data.datasets[0].backgroundColor = makeGradient(
            detailChart.ctx,
            type === 'bar'
                ? (chart === 'orders' ? (isLight() ? 'rgba(249,115,22,0.68)' : 'rgba(249,115,22,0.6)') : (isLight() ? 'rgba(34,197,94,0.68)' : 'rgba(34,197,94,0.6)'))
                : (chart === 'sales'  ? (isLight() ? 'rgba(59,130,246,0.40)'  : 'rgba(59,130,246,0.25)') : (isLight() ? 'rgba(249,115,22,0.42)' : 'rgba(249,115,22,0.25)')),
            type === 'bar'
                ? (chart === 'orders' ? (isLight() ? 'rgba(249,115,22,0.12)' : 'rgba(249,115,22,0.15)') : (isLight() ? 'rgba(34,197,94,0.10)' : 'rgba(34,197,94,0.1)'))
                : (chart === 'sales'  ? (isLight() ? 'rgba(59,130,246,0.04)'  : 'rgba(59,130,246,0)')     : (isLight() ? 'rgba(249,115,22,0.04)' : 'rgba(249,115,22,0)'))
        );
        if (type === 'line') {
            detailChart.data.datasets[0].pointBorderColor = bd;
            detailChart.data.datasets[0].pointBackgroundColor = chart === 'sales' ? blue : orange;
        }
        detailChart.update('none');
    } else {
        // Pie / doughnut — refresh the slice border color only
        detailChart.data.datasets[0].borderColor = bd;
        detailChart.update('none');
    }
};

}); // DOMContentLoaded
</script>
@endpush

@push('styles')
<style>
/* Keep the detail chart a reasonable size on wide screens — it spans a
   full-width card, so fix its height instead of letting maintainAspectRatio
   blow it up to the container's full width. Verbose charts scale down on
   mobile via responsive:true. */
#detailChart {
    width: 100% !important;
    height: 320px !important;
}
/* Pie / doughnut: cap the size so it doesn't render full-card-width. */
@media (min-width: 480px) {
    div[style*="max-width:420px"] #detailChart {
        width: 420px !important;
        height: 320px !important;
    }
}
@media (max-width: 900px) {
    .chart-grid-2 { grid-template-columns: 1fr; }
}
</style>
@endpush
