@extends('dashboard.layout')
@section('title', strtoupper(__('dashboard.stats.kpiRevenue')))

@section('content')

@include('dashboard._permission_check', ['feature' => 'report'])
@php $_permDenied = $GLOBALS['_tronmatix_perm_denied'] ?? false; @endphp

@if(!$_permDenied)

{{-- ── Page Header ──────────────────────────────────────────────────────────────── --}}
<div style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap;
            gap:16px; margin-bottom:20px;">
    <div style="display:flex; align-items:center; gap:14px;">
        <div style="width:48px; height:48px; border-radius:14px; background:rgba(34,197,94,0.12);
                    border:1px solid rgba(34,197,94,0.3); display:flex; align-items:center;
                    justify-content:center; font-size: var(--title-size);">💰</div>
        <div>
            <div style="font-size: var(--title-size); font-weight:900; letter-spacing:3px;">
                {{ strtoupper(__('dashboard.stats.kpiRevenue')) }}
            </div>
            <div style="font-size: var(--title-size); color:var(--text-muted); margin-top:2px;">
                {{ __('dashboard.revenue.pageDesc') }}
            </div>
        </div>
    </div>
</div>

{{-- ── Range Tabs (1 month / 6 months / 1 year) ─────────────────────────────── --}}
<div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:20px;">
    <div style="font-size: var(--title-size); font-weight:700; color:var(--text-muted); letter-spacing:1px; text-transform:uppercase;">
        {{ strtoupper(__('dashboard.revenue.range')) }}:
    </div>
    @php
        $rangeTabs = [
            '1m' => ['label' => '1 MONTH',  'icon' => '📆'],
            '6m' => ['label' => '6 MONTHS', 'icon' => '🗓️'],
            '1y' => ['label' => '1 YEAR',   'icon' => '📅'],
        ];
        $rangeParams = ['range' => $range, 'month' => $month->format('Y-m')];
    @endphp
    @foreach ($rangeTabs as $key => $tab)
        @php $isActive = $range === $key; @endphp
        <a href="{{ route('dashboard.revenue', array_filter(array_merge(['range' => $key], ['month' => $rangeParams['month']]))) }}"
           style="display:inline-flex; align-items:center; gap:6px; padding:7px 16px; border-radius:30px;
                  font-family:Rajdhani, var(--font-kh), sans-serif; font-size: var(--text-sm); font-weight:700;
                  letter-spacing:1px; text-decoration:none; transition:all 0.2s;
                  background: {{ $isActive ? '#22C55E' : 'var(--hover-bg)' }};
                  color:      {{ $isActive ? '#fff' : 'var(--text-muted)' }};
                  border: 1.5px solid {{ $isActive ? '#22C55E' : 'var(--border-input)' }};
                  box-shadow: {{ $isActive ? '0 0 12px rgba(34,197,94,.4)' : 'none' }};"
           onmouseover="this.style.opacity='.82'" onmouseout="this.style.opacity='1'">
            {{ $tab['icon'] }} {{ $tab['label'] }}
        </a>
    @endforeach
    <span style="margin-left:auto;">
        <x-month-selector :month="$month" />
    </span>
</div>

{{-- ── KPI Cards (4) — all clickable ─────────────────────────────────────────── --}}
<div class="stats-grid" style="margin-bottom:24px;">
    <x-kpi-card :label="__('dashboard.stats.kpiRevenue')" value="{{ compact_number($revenue['current'], '$') }}" :trend="$revenue" color="green" href="{{ route('dashboard.orders', ['month' => $month->format('Y-m')]) }}">
        <x-slot name="icon">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="20" height="20"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        </x-slot>
    </x-kpi-card>
    <x-kpi-card :label="__('dashboard.stats.kpiOrders')" value="{{ compact_number($orders['current']) }}" :trend="$orders" color="orange" href="{{ route('dashboard.orders', ['month' => $month->format('Y-m')]) }}">
        <x-slot name="icon">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="20" height="20"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
        </x-slot>
    </x-kpi-card>
    <x-kpi-card :label="__('dashboard.revenue.avgOrder')" value="{{ compact_number($avgOrder['current'], '$') }}" :trend="$avgOrder" color="blue" href="{{ route('dashboard.orders', ['month' => $month->format('Y-m')]) }}">
        <x-slot name="icon">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="20" height="20"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        </x-slot>
    </x-kpi-card>
    <x-kpi-card :label="__('dashboard.stats.kpiCustomers')" value="{{ compact_number($customers['current']) }}" :trend="$customers" color="purple" href="{{ route('dashboard.users') }}">
        <x-slot name="icon">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="20" height="20"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
        </x-slot>
    </x-kpi-card>
</div>

{{-- ── Charts ─────────────────────────────────────────────────────────────────── --}}
<div class="chart-grid-2" style="margin-bottom:20px;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">📈 {{ __('dashboard.revenue.windowRevenue') }}</span>
            <span class="chart-badge">{{ $month->format('F Y') }}</span>
        </div>
        <div class="card-body">
            <canvas id="revenueChart" height="110"></canvas>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <span class="card-title">📅 {{ __('dashboard.revenue.monthlyRevenue') }}</span>
            <span class="chart-badge">{{ __('dashboard.stats.last12Months') }}</span>
        </div>
        <div class="card-body">
            <canvas id="yearChart" height="110"></canvas>
        </div>
    </div>
</div>

{{-- Compare chart — current vs previous window --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <span class="card-title">⚖️ {{ __('dashboard.revenue.compare') }}</span>
        <span class="chart-badge">{{ $month->format('F Y') }}</span>
    </div>
    <div class="card-body">
        <canvas id="compareChart" height="110"></canvas>
    </div>
</div>

@endif

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
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
// Compact number formatter — shows K/M like compact_number() in PHP.
// 1234 → "1.2K"; 1234567 → "1.2M"; 999 → "999"; $ prefix optional.
function fmtK(v, prefix) {
    var s = (prefix || '');
    var n = Number(v) || 0;
    var abs = Math.abs(n);
    if (abs >= 1000000) return s + Number((n / 1000000).toFixed(1)) + 'M';
    if (abs >= 1000)    return s + Number((n / 1000).toFixed(1)) + 'K';
    return s + Math.round(n);
}
function fmtKdollar(v) { return v === 0 ? '' : fmtK(v, '$'); }

// ── Data from Laravel ─────────────────────────────────────────────────────────
const windowLabels   = @json($windowLabels);
const windowRevenue  = @json($windowRevenue);
const windowOrders   = @json($windowOrders);
const compareLabels  = @json($compareLabels);
const compareCurr    = @json($compareCurrent);
const comparePrev    = @json($comparePrevious);
const monthlyLabels  = @json($monthlySalesLabels);
const monthlyRevenue = @json($monthlySalesRevenue);

// ── Palette ───────────────────────────────────────────────────────────────────
const green  = '#22C55E';
const blue   = '#3B82F6';

function makeGradient(ctx, top, bottom) {
    const g = ctx.createLinearGradient(0, 0, 0, 300);
    g.addColorStop(0, top);
    g.addColorStop(1, bottom);
    return g;
}

// Guard: skip if canvas missing (permission denied). Wrapped in an IIFE so
// the early `return` is inside a function (top-level return is a SyntaxError).
(function () {
if (!document.getElementById('revenueChart')) { return; }

// 1. Revenue (line, left axis) + Orders (bar, right axis) — combo chart
const rCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(rCtx, {
    type: 'bar',
    data: { labels: windowLabels, datasets: [
        // Orders — bars on the right axis
        { type:'bar', label:'Orders', data:windowOrders, yAxisID:'yOrders',
          backgroundColor:makeGradient(rCtx,'rgba(249,115,22,0.55)','rgba(249,115,22,0.12)'),
          borderColor:'#F97316', borderWidth:1.5, borderRadius:4, order:2 },
        // Revenue — line on the left axis
        { type:'line', label:'Revenue ($)', data:windowRevenue, yAxisID:'yRevenue',
          borderColor:green, borderWidth:2.5, pointBackgroundColor:green,
          pointBorderColor: isLight() ? '#F1F5F9' : '#111', pointBorderWidth:2, pointRadius:4, pointHoverRadius:7,
          fill:true, backgroundColor:makeGradient(rCtx,'rgba(34,197,94,0.25)','rgba(34,197,94,0)'), tension:0.4, order:1 }]
    },
    options: { responsive:true, layout:{ padding:{ top: 24 } },
        plugins:{ legend:{ position:'top', labels:{ boxWidth:12, padding:16 } },
            datalabels:{ ...dlBase,
                display: function(ctx){ return ctx.dataset.type !== 'bar'; },
                formatter: function(v){ return v === 0 ? '' : fmtK(v, '$'); } },
            tooltip:{ callbacks:{ label: c => c.dataset.label === 'Revenue ($)'
                ? ' '+fmtK(c.parsed.y, '$')
                : ' '+fmtK(c.parsed.y)+' orders' } } },
        scales:{
            x:{ grid:{color: themeColors().grid}, ticks:{ maxTicksLimit:8, maxRotation:45, font:{size: window.innerWidth < 768 ? 9 : 12} } },
            yRevenue:{ position:'left', grid:{color: themeColors().grid}, ticks:{ callback: v => fmtK(v, '$') } },
            yOrders:{ position:'right', grid:{ drawOnChartArea:false }, ticks:{ stepSize:1, precision:0 } }
        }
    }
});

// 2. Last 12 months revenue — Line (blue)
const yCtx = document.getElementById('yearChart').getContext('2d');
const yearChart = new Chart(yCtx, {
    type: 'line',
    data: { labels: monthlyLabels, datasets: [{ label:'Revenue ($)', data:monthlyRevenue,
        borderColor:blue, borderWidth:2.5, pointBackgroundColor:blue,
        pointBorderColor: isLight() ? '#F1F5F9' : '#111', pointBorderWidth:2, pointRadius:4, pointHoverRadius:7,
        fill:true, backgroundColor:makeGradient(yCtx,'rgba(59,130,246,0.25)','rgba(59,130,246,0)'), tension:0.4 }] },
    options: { responsive:true, layout:{ padding:{ top: 24 } }, plugins:{ legend:{display:false}, datalabels:fmtKdollar,
        tooltip:{ callbacks:{ label: c => ' '+fmtK(c.parsed.y, '$') }}},
        scales:{ x:{grid:{color: themeColors().grid}, ticks:{ maxTicksLimit:6, maxRotation:45, font:{size: window.innerWidth < 768 ? 9 : 12} }},
                 y:{grid:{color: themeColors().grid}, ticks:{ callback: v => fmtK(v, '$') }}}}
});

// 3. Compare — current vs previous window, grouped bars
const cCtx = document.getElementById('compareChart').getContext('2d');
const compareChart = new Chart(cCtx, {
    type: 'bar',
    data: { labels: compareLabels, datasets: [
        { label:'Previous', data:comparePrev,
          backgroundColor:'rgba(167,139,250,0.55)', borderColor:'#A78BFA',
          borderWidth:1.5, borderRadius:5, group:'g' },
        { label:'Current', data: compareCurr,
          backgroundColor:makeGradient(cCtx,'rgba(34,197,94,0.6)','rgba(34,197,94,0.15)'),
          borderColor:'#22C55E', borderWidth:1.5, borderRadius:5, group:'g' }
    ] },
    options: { responsive:true, layout:{ padding:{ top: 24 } },
        plugins:{ legend:{ position:'top', labels:{ boxWidth:12, padding:16 } }, datalabels:fmtKdollar },
        scales:{ x:{grid:{color: themeColors().grid}, ticks:{ maxTicksLimit:8, maxRotation:45, font:{size: window.innerWidth < 768 ? 8 : 11} }},
                 y:{grid:{color: themeColors().grid}, ticks:{ callback: v => fmtK(v, '$') }}} }
});

// ── Live Chart theme updater ──────────────────────────────────────────────────
window.__updateChartTheme = function(t) {
    applyChartDefaults();
    const c  = themeColors();
    const bd = t === 'light' ? '#F1F5F9' : '#111';
    [revenueChart, yearChart].forEach(ch => {
        ch.options.scales.x.grid.color = c.grid;
        ch.options.scales.y.grid.color = c.grid;
        ch.update('none');
    });
    // Combo chart: revenue line border/point colour + orders bar gradient
    const revDs = revenueChart.data.datasets.find(d => d.label === 'Revenue ($)');
    if (revDs) {
        revDs.pointBorderColor = bd;
        revDs.borderColor = green;
        revDs.backgroundColor = makeGradient(revenueChart.ctx, 'rgba(34,197,94,0.25)', 'rgba(34,197,94,0)');
    }
    const ordDs = revenueChart.data.datasets.find(d => d.label === 'Orders');
    if (ordDs) {
        ordDs.backgroundColor = makeGradient(revenueChart.ctx, 'rgba(249,115,22,0.55)', 'rgba(249,115,22,0.12)');
    }
    revenueChart.update('none');
    // 12-month chart: point border colour
    yearChart.data.datasets[0].pointBorderColor = bd;
    yearChart.update('none');
    // Compare chart: refresh the current-series gradient (bars need context-sized gradient)
    const cmpCur = compareChart.data.datasets.find(d => d.label === 'Current');
    if (cmpCur) {
        cmpCur.backgroundColor = makeGradient(compareChart.ctx, 'rgba(34,197,94,0.6)', 'rgba(34,197,94,0.15)');
    }
    compareChart.update('none');
};
})(); // end IIFE — keeps the early return legal and charts in scope
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
@media (max-width: 900px) {
    .chart-grid-2 { grid-template-columns: 1fr; }
}
</style>
@endpush
