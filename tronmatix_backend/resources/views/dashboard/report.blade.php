@extends('dashboard.layout')
@section('title', 'REPORTS')

@section('content')

{{-- ── Page Header ──────────────────────────────────────────────────────────────── --}}
<div style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap;
            gap:16px; margin-bottom:28px;">
    <div style="display:flex; align-items:center; gap:14px;">
        <div style="width:48px; height:48px; border-radius:14px; background:rgba(249,115,22,0.12);
                    border:1px solid rgba(249,115,22,0.3); display:flex; align-items:center;
                    justify-content:center; font-size:24px;">📊</div>
        <div>
            <div style="font-size:22px; font-weight:900; letter-spacing:3px;">REPORTS</div>
            <div style="font-size:13px; color:var(--text-muted); margin-top:2px;">
                Analytics overview &amp; data export
            </div>
        </div>
    </div>
    <a href="{{ route('dashboard.index') }}"
       style="display:inline-flex; align-items:center; gap:6px; padding:9px 18px;
              border-radius:9px; border:1px solid rgba(255,255,255,0.1);
              background:rgba(255,255,255,0.04); color:rgba(255,255,255,0.5);
              font-family:Rajdhani,sans-serif; font-size:13px; font-weight:700;
              letter-spacing:1px; text-decoration:none; transition:all .2s;"
       onmouseover="this.style.color='var(--text-primary)'"
       onmouseout="this.style.color='rgba(255,255,255,0.5)'">
        ← BACK TO DASHBOARD
    </a>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════════
     SECTION 1 — EXPORT
══════════════════════════════════════════════════════════════════════════════ --}}
<div class="card" style="margin-bottom:24px; border-color:rgba(249,115,22,0.2);">
    <div class="card-header">
        <div style="display:flex; align-items:center; gap:10px;">
            <div style="width:36px; height:36px; border-radius:9px; background:rgba(249,115,22,0.1);
                        border:1px solid rgba(249,115,22,0.25); display:flex; align-items:center;
                        justify-content:center; font-size:17px; flex-shrink:0;">⬇</div>
            <div>
                <span class="card-title">EXPORT DATA</span>
                <div style="font-size:11px; color:var(--text-muted); margin-top:1px;">
                    Excel exports all 8 sheets &middot; CSV exports Summary sheet only
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">

        {{-- Discount quick-stats row --}}
        <div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:20px;">
            <div style="background:rgba(168,85,247,0.08); border:1px solid rgba(168,85,247,0.2);
                 border-radius:10px; padding:14px 20px; flex:1; min-width:160px;">
                <div style="font-size:11px; color:rgba(255,255,255,0.4); letter-spacing:1.5px; margin-bottom:4px;">THIS MONTH SAVED</div>
                <div style="font-size:26px; font-weight:800; color:#A855F7; letter-spacing:1px;">
                    ${{ number_format($stats['monthly_discount_used'], 2) }}
                </div>
            </div>
            <div style="background:rgba(249,115,22,0.08); border:1px solid rgba(249,115,22,0.2);
                 border-radius:10px; padding:14px 20px; flex:1; min-width:160px;">
                <div style="font-size:11px; color:rgba(255,255,255,0.4); letter-spacing:1.5px; margin-bottom:4px;">DISCOUNT USES (30 DAYS)</div>
                <div style="font-size:26px; font-weight:800; color:#F97316; letter-spacing:1px;">
                    {{ number_format($stats['monthly_discount_count']) }}
                </div>
            </div>
            <div style="background:rgba(34,197,94,0.07); border:1px solid rgba(34,197,94,0.18);
                 border-radius:10px; padding:14px 20px; flex:1; min-width:160px;">
                <div style="font-size:11px; color:rgba(255,255,255,0.4); letter-spacing:1.5px; margin-bottom:4px;">TOTAL REVENUE</div>
                <div style="font-size:26px; font-weight:800; color:#22C55E; letter-spacing:1px;">
                    ${{ number_format($stats['total_revenue'], 0) }}
                </div>
            </div>
            <div style="background:rgba(59,130,246,0.07); border:1px solid rgba(59,130,246,0.18);
                 border-radius:10px; padding:14px 20px; flex:1; min-width:160px;">
                <div style="font-size:11px; color:rgba(255,255,255,0.4); letter-spacing:1.5px; margin-bottom:4px;">TOTAL ORDERS</div>
                <div style="font-size:26px; font-weight:800; color:#3B82F6; letter-spacing:1px;">
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
                <label style="font-size:11px; color:rgba(255,255,255,0.4); letter-spacing:1.5px; font-weight:700;">FROM MONTH</label>
                <input type="month" name="from"
                       class="export-input"
                       value="{{ now()->subMonth()->format('Y-m') }}"
                       max="{{ now()->format('Y-m') }}" />
            </div>

            <div style="display:flex; flex-direction:column; gap:6px;">
                <label style="font-size:11px; color:rgba(255,255,255,0.4); letter-spacing:1.5px; font-weight:700;">TO MONTH</label>
                <input type="month" name="to"
                       class="export-input"
                       value="{{ now()->format('Y-m') }}"
                       max="{{ now()->format('Y-m') }}" />
            </div>

            <div style="display:flex; flex-direction:column; gap:6px;">
                <label style="font-size:11px; color:rgba(255,255,255,0.4); letter-spacing:1.5px; font-weight:700;">FORMAT</label>
                <select name="format" class="export-input export-select">
                    <option value="xlsx">📊 Excel (.xlsx) — All 8 sheets</option>
                    <option value="csv">📄 CSV (.csv) — Summary only</option>
                </select>
            </div>

            <button type="submit" class="btn btn-orange" style="padding:10px 24px; gap:8px;">
                ⬇ EXPORT
            </button>

        </form>

        {{-- Validation error --}}
        @if($errors->has('export'))
        <div style="margin-top:12px; padding:10px 16px; background:rgba(239,68,68,0.1);
             border:1px solid rgba(239,68,68,0.3); border-radius:8px;
             color:#ef4444; font-size:13px; font-weight:600;">
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
        <span class="card-title">🏷️ TOP DISCOUNT CODES THIS MONTH</span>
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

{{-- ══════════════════════════════════════════════════════════════════════════════
     SECTION 3 — CHARTS
══════════════════════════════════════════════════════════════════════════════ --}}

{{-- Row 1: Monthly Revenue + Monthly Orders --}}
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

{{-- Row 2: Daily Sales + User Registrations --}}
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

{{-- Row 3: Order Status Pie + Category Revenue Doughnut --}}
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

{{-- ══════════════════════════════════════════════════════════════════════════════
     SECTION 4 — TOP PRODUCTS + LOW STOCK
══════════════════════════════════════════════════════════════════════════════ --}}
<div class="chart-grid-2" style="margin-bottom:20px;">

    <div class="card">
        <div class="card-header">
            <span class="card-title">🏆 TOP PRODUCTS</span>
            <a href="{{ route('dashboard.products') }}" class="btn btn-outline btn-sm">VIEW ALL</a>
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
            <a href="{{ route('dashboard.products', ['stock'=>'low']) }}" class="btn btn-outline btn-sm">VIEW ALL</a>
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

// ── Data from Laravel ─────────────────────────────────────────────────────────
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
        scales:{ x:{grid:{color: themeColors().grid}}, y:{grid:{color: themeColors().grid},
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
        scales:{ x:{grid:{color: themeColors().grid}}, y:{grid:{color: themeColors().grid},
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
        scales:{ x:{grid:{color: themeColors().grid}}, y:{grid:{color: themeColors().grid},
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
    font-size: 11px;
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
    font-size: 14px;
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
