<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TRONMATIX — Dashboard Report</title>
    <style>
        /* ── Fonts ──────────────────────────────────────────────────────────── */
        @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Inter:wght@400;600;700&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1E1E2E;
            line-height: 1.5;
            padding: 0;
        }

        /* ── Cover / Header ─────────────────────────────────────────────────── */
        .cover {
            text-align: center;
            padding: 50px 20px 30px;
            border-bottom: 3px solid #F97316;
            margin-bottom: 24px;
        }
        .cover .logo {
            font-family: 'Rajdhani', 'DejaVu Sans', sans-serif;
            font-size: 36px;
            font-weight: 700;
            color: #F97316;
            letter-spacing: 4px;
        }
        .cover h1 {
            font-family: 'Rajdhani', 'DejaVu Sans', sans-serif;
            font-size: 22px;
            font-weight: 600;
            color: #1E1E2E;
            margin-top: 4px;
        }
        .cover .subtitle {
            font-size: 12px;
            color: #888;
            margin-top: 6px;
        }
        .cover .period {
            font-size: 13px;
            color: #F97316;
            font-weight: 600;
            margin-top: 8px;
        }

        /* ── Section headers ───────────────────────────────────────────────── */
        h2 {
            font-family: 'Rajdhani', 'DejaVu Sans', sans-serif;
            font-size: 16px;
            font-weight: 700;
            color: #1E1E2E;
            border-bottom: 2px solid #F97316;
            padding-bottom: 4px;
            margin: 28px 0 14px;
            letter-spacing: 1px;
        }
        h3 {
            font-family: 'Rajdhani', 'DejaVu Sans', sans-serif;
            font-size: 13px;
            font-weight: 700;
            color: #F97316;
            margin: 16px 0 8px;
        }

        /* ── KPI cards row ─────────────────────────────────────────────────── */
        .kpi-row {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .kpi-card {
            flex: 1;
            min-width: 100px;
            background: #FFF8F3;
            border: 1px solid #FED7AA;
            border-radius: 8px;
            padding: 12px 14px;
            text-align: center;
        }
        .kpi-card .kpi-label {
            font-size: 8px;
            font-weight: 700;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1.2px;
        }
        .kpi-card .kpi-value {
            font-family: 'Rajdhani', 'DejaVu Sans', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: #F97316;
            margin-top: 2px;
        }
        .kpi-card .kpi-period {
            font-size: 8px;
            color: #aaa;
        }

        /* ── Tables ────────────────────────────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 9.5px;
        }
        th {
            background: #1E1E2E;
            color: #fff;
            font-weight: 700;
            padding: 7px 8px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #E8E4E0;
        }
        tr:nth-child(even) td {
            background: #FAF8F6;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-orange { color: #F97316; font-weight: 700; }
        .text-green  { color: #16A34A; font-weight: 700; }
        .text-blue   { color: #2563EB; font-weight: 700; }
        .text-red    { color: #DC2626; font-weight: 700; }
        .text-purple { color: #7C3AED; font-weight: 700; }

        /* Status badges */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-delivered  { background: #D1FAE5; color: #16A34A; }
        .badge-shipped    { background: #DBEAFE; color: #2563EB; }
        .badge-processing { background: #E0F2FE; color: #0891B2; }
        .badge-confirmed  { background: #EDE9FE; color: #7C3AED; }
        .badge-cancelled  { background: #FEE2E2; color: #DC2626; }
        .badge-pending    { background: #FEF3C7; color: #D97706; }

        /* Total row */
        .total-row td {
            font-weight: 700;
            background: #1E1E2E !important;
            color: #F97316;
            border-top: 2px solid #F97316;
        }

        /* ── Two-column layout ─────────────────────────────────────────────── */
        .two-col {
            display: flex;
            gap: 16px;
        }
        .two-col > div { flex: 1; }

        /* ── Footer ────────────────────────────────────────────────────────── */
        .footer {
            text-align: center;
            font-size: 8px;
            color: #aaa;
            margin-top: 30px;
            padding-top: 12px;
            border-top: 1px solid #E8E4E0;
        }

        .page-break { page-break-before: always; }
    </style>
</head>
<body>

    {{-- ═══════════════════════════════════════════════════════════════════════
         COVER / HEADER
    ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="cover">
        <div class="logo">⚡ TRONMATIX</div>
        <h1>Dashboard Performance Report</h1>
        <div class="subtitle">Auto-generated &middot; {{ $generated_at }}</div>
        <div class="period">{{ $period }}</div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         SECTION 1 — KPI SUMMARY
    ═══════════════════════════════════════════════════════════════════════ --}}
    <h2>📊 Executive Summary</h2>

    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-label">Total Revenue (All Time)</div>
            <div class="kpi-value">${{ number_format($totalRevenue, 0) }}</div>
            <div class="kpi-period">Period: ${{ number_format($periodRevenue, 0) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Total Orders</div>
            <div class="kpi-value">{{ number_format($allOrders) }}</div>
            <div class="kpi-period">Period: {{ number_format($periodOrders) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Discount Saved</div>
            <div class="kpi-value">${{ number_format($totalDiscount, 0) }}</div>
            <div class="kpi-period">Period: ${{ number_format($periodDiscount, 0) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Users</div>
            <div class="kpi-value">{{ number_format(\App\Models\User::count()) }}</div>
            <div class="kpi-period">Products: {{ number_format(\App\Models\Product::count()) }}</div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         SECTION 2 — ORDER STATUS
    ═══════════════════════════════════════════════════════════════════════ --}}
    <h2>📋 Order Status Breakdown</h2>

    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th class="text-right">Count</th>
                <th class="text-right">%</th>
                <th class="text-right">Revenue ($)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($statusData as $s)
            <tr>
                <td><span class="badge badge-{{ $s['label'] }}">{{ $s['label'] }}</span></td>
                <td class="text-right">{{ number_format($s['count']) }}</td>
                <td class="text-right">{{ $s['pct'] }}%</td>
                <td class="text-right text-orange">${{ number_format($s['revenue'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td>TOTAL</td>
                <td class="text-right">{{ number_format(collect($statusData)->sum('count')) }}</td>
                <td class="text-right">100%</td>
                <td class="text-right">${{ number_format(collect($statusData)->sum('revenue'), 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- ═══════════════════════════════════════════════════════════════════════
         SECTION 3 — MONTHLY TREND
    ═══════════════════════════════════════════════════════════════════════ --}}
    <h2>📈 Monthly Trend (Last 12 Months)</h2>

    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th class="text-right">Revenue ($)</th>
                <th class="text-right">Orders</th>
                <th class="text-right">Discount Saved ($)</th>
                <th class="text-right">Avg Order Value ($)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($monthlySales as $m)
            <tr>
                <td><strong>{{ $m['label'] }}</strong></td>
                <td class="text-right text-orange">${{ number_format($m['revenue'], 2) }}</td>
                <td class="text-right">{{ number_format($m['orders']) }}</td>
                <td class="text-right text-purple">${{ number_format($m['saved'], 2) }}</td>
                <td class="text-right">{{ $m['orders'] > 0 ? '$' . number_format($m['revenue'] / $m['orders'], 2) : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td>TOTAL / AVG</td>
                <td class="text-right">${{ number_format(collect($monthlySales)->sum('revenue'), 2) }}</td>
                <td class="text-right">{{ number_format(collect($monthlySales)->sum('orders')) }}</td>
                <td class="text-right">${{ number_format(collect($monthlySales)->sum('saved'), 2) }}</td>
                <td class="text-right">${{ number_format(collect($monthlySales)->where('orders', '>', 0)->avg(fn($m) => $m['revenue'] / $m['orders']) ?: 0, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- ═══════════════════════════════════════════════════════════════════════
         SECTION 4 — CATEGORY + TOP PRODUCTS (two columns)
    ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="page-break"></div>

    <div class="two-col">
        <div>
            <h2>🍩 Revenue by Category</h2>
            <table>
                <thead>
                    <tr><th>Category</th><th class="text-right">Units</th><th class="text-right">Revenue ($)</th><th class="text-right">%</th></tr>
                </thead>
                <tbody>
                    @foreach($categoryRevenue as $c)
                    <tr>
                        <td><strong>{{ $c->category }}</strong></td>
                        <td class="text-right">{{ number_format($c->units_sold) }}</td>
                        <td class="text-right text-orange">${{ number_format($c->revenue, 2) }}</td>
                        <td class="text-right">{{ round($c->revenue / $grandTotalRevenue * 100, 1) }}%</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td>TOTAL</td>
                        <td class="text-right">{{ number_format($categoryRevenue->sum('units_sold')) }}</td>
                        <td class="text-right">${{ number_format($categoryRevenue->sum('revenue'), 2) }}</td>
                        <td class="text-right">100%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div>
            <h2>🏆 Top 10 Products</h2>
            <table>
                <thead>
                    <tr><th>#</th><th>Product</th><th class="text-right">Sold</th><th class="text-right">Revenue ($)</th></tr>
                </thead>
                <tbody>
                    @foreach($topProducts as $i => $p)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td><strong>{{ \Illuminate\Support\Str::limit($p->name, 28) }}</strong></td>
                        <td class="text-right">{{ number_format($p->units_sold) }}</td>
                        <td class="text-right text-orange">${{ number_format($p->item_revenue, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td></td>
                        <td>TOTAL</td>
                        <td class="text-right">{{ number_format($topProducts->sum('units_sold')) }}</td>
                        <td class="text-right">${{ number_format($topProducts->sum('item_revenue'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         SECTION 5 — DISCOUNT CODES (top 8)
    ═══════════════════════════════════════════════════════════════════════ --}}
    <h2 style="margin-top: 28px;">🏷️ Top Discount Codes (Period)</h2>

    @php
        $topDiscounts = \App\Models\Discount::select(
                'discounts.*',
                \Illuminate\Support\Facades\DB::raw('COUNT(orders.id) as period_uses'),
                \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(orders.discount_amount), 0) as period_saved')
            )
            ->leftJoin('orders', function ($j) use ($from, $to) {
                $j->on('orders.discount_id', '=', 'discounts.id')
                  ->whereBetween('orders.created_at', [$from, $to]);
            })
            ->groupBy('discounts.id')
            ->orderByDesc('period_saved')
            ->limit(8)
            ->get();
    @endphp

    @if($topDiscounts->isNotEmpty())
    <table>
        <thead>
            <tr><th>Code</th><th>Type</th><th>Value</th><th class="text-right">Uses</th><th class="text-right">Saved ($)</th><th>Status</th></tr>
        </thead>
        <tbody>
            @foreach($topDiscounts as $d)
            <tr>
                <td><strong style="color:#F97316;">{{ $d->code }}</strong></td>
                <td>{{ strtoupper($d->type) }}</td>
                <td>{{ $d->type === 'percentage' ? $d->value . '%' : '$' . number_format($d->value, 2) }}</td>
                <td class="text-right">{{ number_format($d->period_uses) }}</td>
                <td class="text-right text-purple">${{ number_format($d->period_saved, 2) }}</td>
                <td><span class="badge badge-{{ $d->status === 'active' ? 'delivered' : ($d->status === 'expired' ? 'cancelled' : 'pending') }}">{{ $d->status }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="color:#888; font-style:italic;">No discounts used during this period.</p>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════
         FOOTER
    ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="footer">
        <strong>TRONMATIX COMPUTER</strong> &middot; Phnom Penh, Cambodia &middot;
        Generated {{ $generated_at }} &middot; Page {PAGE_NUM} of {PAGE_COUNT}
    </div>

</body>
</html>
