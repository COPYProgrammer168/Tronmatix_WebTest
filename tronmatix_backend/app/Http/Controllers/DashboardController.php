<?php

// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Models\Banner;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Exports\DashboardExport;
use App\Services\TelegramService;      // Bot 1 — admin/owner alerts
use App\Services\TelegramBotService;   // Bot 2 — user-facing notifications
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\MetricComparisonService;
use App\Traits\StorageHelper;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    use StorageHelper;
    // ── Dashboard index ───────────────────────────────────────────────────────
    public function index(Request $request, MetricComparisonService $comparison)
    {
        $month = now();
        if ($request->filled('month')) {
            try {
                // FIX: append '-01' so the day is explicit. createFromFormat('Y-m', ...)
                // fills the missing day/time from the CURRENT date in PHP 8.x, which
                // can roll the month forward (e.g. "2026-06" → July) and break the
                // daily charts + prev/next navigation.
                $month = Carbon::createFromFormat('Y-m-d', $request->input('month') . '-01');
            } catch (\Throwable $e) {
                // Invalid month format — silently use current month
                $month = now();
            }
        }

        $orders    = $comparison->compare(Order::query(), 'created_at', $month, 'count');
        $revenue   = $comparison->compare(
            Order::whereNotIn('status', ['cancelled']), 'created_at', $month, 'sum', 'total'
        );
        $customers = $comparison->compare(User::query(), 'created_at', $month, 'count');

        $data = $this->buildDashboardData($month);
        $data['month']     = $month;
        $data['orders']    = $orders;
        $data['revenue']   = $revenue;
        $data['customers'] = $customers;

        return view('dashboard.index', $data);
    }

    public function report(Request $request, MetricComparisonService $comparison)
    {
        $month = $request->filled('month')
            ? Carbon::createFromFormat('Y-m-d', $request->input('month') . '-01')
            : now();

        // ── Period selector ──────────────────────────────────────────────────────
        // The period select drives the KPI cards AND the export range. For
        // month-based periods we reuse the MetricComparisonService (current vs
        // previous month). For wider ranges we aggregate over the full range and
        // compare against the immediately preceding period of equal length.
        $validPeriods = ['today', 'this-month', '6-months', 'this-year', 'about'];
        $period = in_array($request->input('period'), $validPeriods, true)
            ? $request->input('period')
            : 'this-month';

        [$curStart, $curEnd, $prevStart, $prevEnd] = $this->periodRange($period, $month);

        $orders = $this->periodCompare(Order::query(), 'created_at', $curStart, $curEnd, $prevStart, $prevEnd, 'count');
        $revenue = $this->periodCompare(
            Order::whereNotIn('status', ['cancelled']), 'created_at',
            $curStart, $curEnd, $prevStart, $prevEnd, 'sum', 'total'
        );
        $customers = $this->periodCompare(User::query(), 'created_at', $curStart, $curEnd, $prevStart, $prevEnd, 'count');

        $data = $this->buildDashboardData($month);
        $data['month']     = $month;
        $data['orders']    = $orders;
        $data['revenue']   = $revenue;
        $data['customers'] = $customers;
        $data['period']    = $period;

        return view('dashboard.report', $data);
    }

    // ── Revenue detail (KPI card drill-down) ────────────────────────────────
    // Shows Revenue with the same KPI-card compare style across 1 month,
    // 6 months and 1 year, plus a pair of charts.
    public function revenue(Request $request, MetricComparisonService $comparison)
    {
        $month = $request->filled('month')
            ? Carbon::createFromFormat('Y-m-d', $request->input('month') . '-01')
            : now();

        // ── KPI cards: current vs previous over each window ─────────────────
        // 1 month  → current calendar month vs previous calendar month
        // 6 months → last 6 calendar months vs the 6 before them
        // 1 year   → last 12 calendar months vs the 12 before them
        $validRanges = ['1m', '6m', '1y'];
        $range = in_array($request->input('range'), $validRanges, true)
            ? $request->input('range')
            : '1m';

        $baseRev = Order::whereNotIn('status', ['cancelled']);

        $windowStart = fn (int $months) => $month->copy()->startOfMonth()->subMonths($months - 1);
        $windowEnd   = fn () => $month->copy()->endOfMonth();

        [$curStart, $curEnd, $prevStart, $prevEnd] = match ($range) {
            '6m' => [
                $windowStart(6), $windowEnd(),
                $windowStart(6)->subMonths(6), $month->copy()->startOfMonth()->subMonths(6)->endOfMonth(),
            ],
            '1y' => [
                $windowStart(12), $windowEnd(),
                $windowStart(12)->subMonths(12), $month->copy()->startOfMonth()->subMonths(12)->endOfMonth(),
            ],
            default => [
                $month->copy()->startOfMonth(), $month->copy()->endOfMonth(),
                $month->copy()->subMonth()->startOfMonth(), $month->copy()->subMonth()->endOfMonth(),
            ],
        };

        $revenue = $this->periodCompare(
            clone $baseRev, 'created_at',
            $curStart, $curEnd, $prevStart, $prevEnd, 'sum', 'total'
        );
        $orders = $this->periodCompare(
            Order::query(), 'created_at',
            $curStart, $curEnd, $prevStart, $prevEnd, 'count'
        );
        $avgOrder = $this->periodCompare(
            clone $baseRev, 'created_at',
            $curStart, $curEnd, $prevStart, $prevEnd, 'avg', 'total'
        );
        $customers = $this->periodCompare(
            User::query(), 'created_at',
            $curStart, $curEnd, $prevStart, $prevEnd, 'count'
        );

        // ── Charts ──────────────────────────────────────────────────────────
        // Chart 1 — revenue (line) + orders (bar) across the selected window.
        // Both summed per day so they can share a time axis at any granularity.
        $seriesStart = $curStart->copy();
        $seriesEnd   = $curEnd->copy();
        $windowRows = (clone $baseRev)
            ->select(
                DB::raw($this->dateFormatExpr('created_at', 'Y-m-d') . ' as day_key'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->whereBetween('created_at', [$seriesStart, $seriesEnd])
            ->groupBy('day_key')->orderBy('day_key')
            ->get()->keyBy('day_key');

        $windowLabels = $windowRevenue = $windowOrders = [];
        if ($range === '1m') {
            // Daily granularity for the current month. Only include days that
            // actually have revenue or orders — empty days are skipped.
            for ($d = $curStart->copy(); $d->lte($curEnd); $d->addDay()) {
                $found = $windowRows->get($d->toDateString());
                $rev = $found ? round((float) $found->revenue, 2) : 0;
                $cnt = $found ? (int) $found->orders : 0;
                if ($rev <= 0 && $cnt <= 0) continue;
                $windowLabels[] = $d->format('j M');
                $windowRevenue[] = $rev;
                $windowOrders[]  = $cnt;
            }
        } else {
            // Monthly granularity for 6m / 1y. Skip months with no activity.
            for ($i = 0; $i < ($range === '6m' ? 6 : 12); $i++) {
                $m = $curStart->copy()->addMonths($i);
                $found = $windowRows->filter(fn ($row, $key) => str_starts_with($key, $m->format('Y-m')))->values();
                $rev = $found->sum(fn ($row) => (float) $row->revenue);
                $cnt = $found->sum(fn ($row) => (int) $row->orders);
                if ($rev <= 0 && $cnt <= 0) continue;
                $windowLabels[] = $m->format('M Y');
                $windowRevenue[] = $rev;
                $windowOrders[]  = $cnt;
            }
        }

        // ── Compare chart ───────────────────────────────────────────────────
        // Grouped bars: each period (day for 1m, month for 6m/1y) of the
        // current window vs the matching period one window earlier, so the
        // user can see day-by-day / month-by-month growth at a glance.
        $compareLabels = $compareCurrent = $comparePrevious = [];
        $shiftBy = $range === '1m' ? '1 month' : ($range === '6m' ? '6 months' : '1 year');

        // Previous-window rows: shift the whole window back and aggregate the same way.
        $prevRows = (clone $baseRev)
            ->select(
                DB::raw($this->dateFormatExpr('created_at', 'Y-m-d') . ' as day_key'),
                DB::raw('SUM(total) as revenue')
            )
            ->whereBetween('created_at', [$curStart->copy()->subMonths($range === '1m' ? 1 : ($range === '6m' ? 6 : 12)), $curEnd->copy()->subMonths($range === '1m' ? 1 : ($range === '6m' ? 6 : 12))])
            ->groupBy('day_key')->orderBy('day_key')
            ->get()->keyBy('day_key');

        if ($range === '1m') {
            // Daily granularity — pair each current day with the same day last month.
            for ($d = $curStart->copy(); $d->lte($curEnd); $d->addDay()) {
                $cur = $windowRows->get($d->toDateString());
                $prev = $prevRows->get($d->copy()->subMonth()->toDateString());
                $curRev = $cur ? round((float) $cur->revenue, 2) : 0;
                $prevRev = $prev ? round((float) $prev->revenue, 2) : 0;
                if ($curRev <= 0 && $prevRev <= 0) continue;
                $compareLabels[] = $d->format('j M');
                $compareCurrent[] = $curRev;
                $comparePrevious[] = $prevRev;
            }
        } else {
            // Monthly granularity — pair each current month with the same month a window earlier.
            for ($i = 0; $i < ($range === '6m' ? 6 : 12); $i++) {
                $m = $curStart->copy()->addMonths($i);
                $cur = $windowRows->filter(fn ($row, $key) => str_starts_with($key, $m->format('Y-m')))->values();
                $pm = $m->copy()->subMonths($range === '6m' ? 6 : 12);
                $prev = $prevRows->filter(fn ($row, $key) => str_starts_with($key, $pm->format('Y-m')))->values();
                $curRev = $cur->sum(fn ($row) => (float) $row->revenue);
                $prevRev = $prev->sum(fn ($row) => (float) $row->revenue);
                if ($curRev <= 0 && $prevRev <= 0) continue;
                $compareLabels[] = $m->format('M y');
                $compareCurrent[] = $curRev;
                $comparePrevious[] = $prevRev;
            }
        }

        // Chart 2 — last 12 months monthly revenue (line).
        $monthlySalesLabels = $monthlySalesRevenue = [];
        $salesRows12 = (clone $baseRev)
            ->select(
                DB::raw($this->dateFormatExpr('created_at', 'Y-m') . ' as month_key'),
                DB::raw('SUM(total) as revenue')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('month_key')->orderBy('month_key')
            ->get()->keyBy('month_key');

        for ($i = 11; $i >= 0; $i--) {
            $date  = Carbon::now()->subMonths($i);
            $key   = $date->format('Y-m');
            $found = $salesRows12->get($key);
            $monthlySalesLabels[] = $date->format('M Y');
            $monthlySalesRevenue[] = $found ? round((float) $found->revenue, 2) : 0;
        }

        return view('dashboard.revenue', compact(
            'month', 'range', 'revenue', 'orders', 'avgOrder', 'customers',
            'windowLabels', 'windowRevenue', 'windowOrders',
            'compareLabels', 'compareCurrent', 'comparePrevious',
            'monthlySalesLabels', 'monthlySalesRevenue'
        ));
    }

    /**
     * Return [currentStart, currentEnd, prevStart, prevEnd] Carbon dates for a
     * period relative to the reference month. For 'about' (all-time) the current
     * range is open-ended and there is no meaningful previous range.
     */
    private function periodRange(string $period, Carbon $month): array
    {
        $now = Carbon::now();

        return match ($period) {
            'today' => [
                $now->copy()->startOfDay(), $now->copy()->endOfDay(),
                $now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay(),
            ],
            '6-months' => [
                $now->copy()->subMonths(5)->startOfMonth(), $now->copy()->endOfMonth(),
                $now->copy()->subMonths(11)->startOfMonth(), $now->copy()->subMonths(6)->endOfMonth(),
            ],
            'this-year' => [
                $now->copy()->startOfYear(), $now->copy()->endOfDay(),
                $now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear(),
            ],
            'about' => [
                null, null, null, null,
            ],
            default => [ // 'this-month' and any fallback
                $month->copy()->startOfMonth(), $month->copy()->endOfMonth(),
                $month->copy()->subMonth()->startOfMonth(), $month->copy()->subMonth()->endOfMonth(),
            ],
        };
    }

    /**
     * Aggregate a metric over a current range and compare against a previous
     * range, mirroring MetricComparisonService::compare() but range-based.
     */
    private function periodCompare(
        $query,
        string $dateCol,
        $curStart,
        $curEnd,
        $prevStart,
        $prevEnd,
        string $agg,
        ?string $sumCol = null
    ): array {
        $aggFn = fn ($start, $end) => match ($agg) {
            'sum' => (float) (clone $query)->whereBetween($dateCol, [$start, $end])->sum($sumCol ?? 'total'),
            'avg' => (float) (clone $query)->whereBetween($dateCol, [$start, $end])->avg($sumCol ?? 'total'),
            default => (int) (clone $query)->whereBetween($dateCol, [$start, $end])->count(),
        };

        // 'about' (all-time): current = everything, no previous range.
        if ($curStart === null) {
            $current  = match ($agg) {
                'sum' => (float) $query->sum($sumCol ?? 'total'),
                'avg' => (float) $query->avg($sumCol ?? 'total'),
                default => (int) $query->count(),
            };
            return ['current' => $current, 'previous' => 0, 'trend' => 'flat', 'pct' => 0];
        }

        $current  = $aggFn($curStart, $curEnd);
        $previous = $aggFn($prevStart, $prevEnd);

        $trend = 'flat';
        $pct   = 0;
        if ($previous != 0) {
            $pct   = round((($current - $previous) / $previous) * 100);
            $trend = $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'flat');
        } elseif ($current > 0) {
            $trend = 'up';
            $pct   = 100;
        }

        return ['current' => $current, 'previous' => $previous, 'trend' => $trend, 'pct' => abs($pct)];
    }

    // ── Shared analytics data builder ─────────────────────────────────────────
    private function buildDashboardData(?Carbon $month = null): array
    {
        $month = $month ?? now();
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth   = $month->copy()->endOfMonth();

        $stats = [
            'total_users'            => User::count(),
            'total_products'         => Product::count(),
            'total_orders'           => Order::count(),
            'total_revenue'          => Order::whereNotIn('status', ['cancelled'])->sum('total'),
            'pending_orders'         => Order::whereIn('status', ['pending', 'confirmed'])->count(),
            'active_orders'          => Order::whereIn('status', ['confirmed', 'processing', 'shipped'])->count(),
            'total_discount_used'    => (float) Order::whereNotNull('discount_amount')
                                            ->where('discount_amount', '>', 0)->sum('discount_amount'),
            'active_discounts'       => Discount::active()->count(),
            'monthly_discount_used'  => (float) Order::whereNotNull('discount_amount')
                                            ->where('discount_amount', '>', 0)
                                            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                                            ->sum('discount_amount'),
            'monthly_discount_count' => Order::whereNotNull('discount_id')
                                            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                                            ->count(),
            // ── Inventory widgets (Prompt 6) ─────────────────────────────
            // Total inventory value = SUM(current_stock * cost_price), treating
            // NULL cost_price as 0 so a missing cost doesn't break the total.
            'inventory_value'     => (float) Product::selectRaw('COALESCE(SUM(current_stock * COALESCE(cost_price, 0)), 0) as total')->value('total'),
            // How many products are missing a cost price (surfaced so the figure
            // above is understood to be an undercount, not silently wrong).
            'no_cost_products'    => (int) Product::whereNull('cost_price')->orWhere('cost_price', 0)->count(),
            // Low stock count uses each product's low_stock_threshold column,
            // falling back to the global setting when the column is 0/unset.
            'low_stock_count'     => (int) Product::where('current_stock', '>', 0)
                ->whereRaw('current_stock <= COALESCE(NULLIF(low_stock_threshold, 0), ?)', [(int) AdminSetting::int('notif_low_stock_threshold', 5)])
                ->count(),
        ];

        // ── Daily revenue & orders for selected month ─────────────────────────
        $monthDailyRows = Order::select(
            DB::raw($this->dateFormatExpr('created_at', 'Y-m-d') . ' as day_key'),
            DB::raw('SUM(total) as revenue'),
            DB::raw('COUNT(*) as orders')
        )
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereNotIn('status', ['cancelled'])
            ->groupBy('day_key')->orderBy('day_key')
            ->get()->keyBy('day_key');

        $monthRevenueDaily = $monthOrdersDaily = [];
        $monthDailyLabels = [];
        foreach ($monthDailyRows as $key => $row) {
            $date = Carbon::parse($key);
            $monthDailyLabels[] = $date->format('j M');
            $monthRevenueDaily[] = round((float) $row->revenue, 2);
            $monthOrdersDaily[]  = (int) $row->orders;
        }

        // Monthly user registrations (still last 12 months for the user chart)
        $userRows = User::select(
            DB::raw($this->dateFormatExpr('created_at', 'Y-m') . ' as month_key'),
            DB::raw('COUNT(*) as total')
        )
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('month_key')->orderBy('month_key')
            ->get()->keyBy('month_key');

        $monthlyUserLabels = $monthlyUserCounts = [];
        for ($i = 11; $i >= 0; $i--) {
            $date  = Carbon::now()->subMonths($i);
            $key   = $date->format('Y-m');
            $found = $userRows->get($key);
            $monthlyUserLabels[] = $date->format('M Y');
            $monthlyUserCounts[] = $found ? (int) $found->total : 0;
        }

        // ── Monthly revenue for last 12 months ──────────────────────────────
        $monthlySalesLabels = $monthlySalesRevenue = [];
        $salesRows12 = Order::select(
            DB::raw($this->dateFormatExpr('created_at', 'Y-m') . ' as month_key'),
            DB::raw('SUM(total) as revenue')
        )
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->whereNotIn('status', ['cancelled'])
            ->groupBy('month_key')->orderBy('month_key')
            ->get()->keyBy('month_key');

        $monthlyOrders = [];
        $orderRows12 = Order::select(
            DB::raw($this->dateFormatExpr('created_at', 'Y-m') . ' as month_key'),
            DB::raw('COUNT(*) as total')
        )
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->whereNotIn('status', ['cancelled'])
            ->groupBy('month_key')->orderBy('month_key')
            ->get()->keyBy('month_key');

        for ($i = 11; $i >= 0; $i--) {
            $date  = Carbon::now()->subMonths($i);
            $key   = $date->format('Y-m');
            $found = $salesRows12->get($key);
            $monthlySalesLabels[] = $date->format('M Y');
            $monthlySalesRevenue[] = $found ? round((float) $found->revenue, 2) : 0;
            $foundOrder = $orderRows12->get($key);
            $monthlyOrders[] = $foundOrder ? (int) $foundOrder->total : 0;
        }

        // Order status pie
        $orderStatus  = Order::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')->get()
            ->mapWithKeys(fn($item) => [$item->status => (int) $item->total]);

        $statusLabels = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        $statusCounts = collect($statusLabels)->map(fn($s) => $orderStatus->get($s, 0))->values()->toArray();

        // Sales by category
        $categoryRevenue = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders',   'order_items.order_id',   '=', 'orders.id')
            ->whereNotIn('orders.status', ['cancelled'])
            ->select('products.category', DB::raw('SUM(order_items.price * order_items.qty) as revenue'))
            ->groupBy('products.category')->orderByDesc('revenue')->limit(6)->get();

        $categoryLabels  = $categoryRevenue->pluck('category')->toArray();
        $categoryRevData = $categoryRevenue->pluck('revenue')->map(fn($v) => round((float) $v, 2))->toArray();

        // Daily sales — last 14 days
        $dailyRows = Order::select(
            DB::raw($this->dateFormatExpr('created_at', 'Y-m-d') . ' as date_key'),
            DB::raw('SUM(total) as revenue')
        )
            ->where('created_at', '>=', Carbon::now()->subDays(13)->startOfDay())
            ->whereNotIn('status', ['cancelled'])
            ->groupBy('date_key')->orderBy('date_key')
            ->get()->keyBy('date_key');

        $dailyLabels = $dailyRevenue = [];
        for ($i = 13; $i >= 0; $i--) {
            $date  = Carbon::now()->subDays($i);
            $key   = $date->toDateString();
            $found = $dailyRows->get($key);
            $dailyLabels[]  = $date->format('d M');
            $dailyRevenue[] = $found ? round((float) $found->revenue, 2) : 0;
        }

        $top_products = Product::withCount('orderItems')->orderByDesc('order_items_count')->take(5)->get();
        $low_stock    = Product::lowStock()->orderBy('current_stock')->take(5)->get();
        $recent_orders = Order::with(['user', 'items', 'location'])->latest()->take(14)->get();

        $top_discount_codes = Discount::select(
            'discounts.*',
            DB::raw('COUNT(orders.id)                          AS monthly_uses'),
            DB::raw('COALESCE(SUM(orders.discount_amount), 0) AS monthly_saved')
        )
            ->join('orders', function ($join) use ($startOfMonth, $endOfMonth) {
                $join->on('orders.discount_id', '=', 'discounts.id')
                    ->whereBetween('orders.created_at', [$startOfMonth, $endOfMonth]);
            })
            ->groupBy('discounts.id')
            ->orderByDesc('monthly_saved')
            ->limit(8)->get();

        return compact(
            'stats', 'recent_orders', 'top_products', 'low_stock',
            'monthlyUserCounts', 'monthlyUserLabels',
            'statusLabels', 'statusCounts', 'categoryLabels', 'categoryRevData',
            'dailyLabels', 'dailyRevenue', 'top_discount_codes',
            'monthDailyLabels', 'monthRevenueDaily', 'monthOrdersDaily', 'month',
            'monthlySalesLabels', 'monthlySalesRevenue', 'monthlyOrders'
        );
    }

    // ── Products list ─────────────────────────────────────────────────────────
    public function products()
    {
        $query = Product::query();

        if (request('search')) {
            $term = '%' . request('search') . '%';
            $query->where(fn($q) => $q->where('name', 'LIKE', $term)
                ->orWhere('brand', 'LIKE', $term)
                ->orWhere('category', 'LIKE', $term));
        }
        if (request('category')) {
            $query->whereRaw('LOWER(category) = ?', [strtolower(request('category'))]);
        }

        // FIX [2]: low/in stock filters use AdminSetting threshold
        $threshold = AdminSetting::int('notif_low_stock_threshold', 5);
        if (request('stock') === 'out') {
            $query->where('stock', '<=', 0);
        } elseif (request('stock') === 'low') {
            $query->where('stock', '>', 0)->where('stock', '<=', $threshold); // FIX [2]
        } elseif (request('stock') === 'in') {
            $query->where(fn($q) => $q->whereNull('stock')->orWhere('stock', '>', 0));
        }

        if (request('filter') === 'hot') {
            $query->where('is_hot', true);
        } elseif (request('filter') === 'featured') {
            $query->where('is_featured', true);
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        return view('dashboard.products', compact('products'));
    }

    // ── Create / Store product ────────────────────────────────────────────────
    public function createProduct()
    {
        return view('dashboard.products-form', ['product' => null]);
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:100',
            'brand' => 'nullable|string|max:100',
            'stock' => 'nullable|integer|min:0',
            'rating' => 'nullable|numeric|min:0|max:5',
            'is_featured' => 'nullable|boolean',
            'is_hot' => 'nullable|boolean',
        ]);
        $validated['is_featured'] = $request->has('is_featured');
        $validated['is_hot'] = $request->has('is_hot');
        Product::create(array_merge($validated, ['images' => $this->processImages($request, [])]));

        return redirect()->route('dashboard.products')->with('success', 'Product created.');
    }

    public function editProduct(Product $product)
    {
        return view('dashboard.products-form', compact('product'));
    }

    public function updateProduct(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:100',
            'brand' => 'nullable|string|max:100',
            'stock' => 'nullable|integer|min:0',
            'rating' => 'nullable|numeric|min:0|max:5',
            'is_featured' => 'nullable|boolean',
            'is_hot' => 'nullable|boolean',
        ]);
        $validated['is_featured'] = $request->has('is_featured');
        $validated['is_hot'] = $request->has('is_hot');

        if ($request->boolean('remove_image')) {
            foreach ($product->all_images as $path) {
                $this->deleteStoredFile($path);
            }
            $images = [];
        } else {
            $images = $this->processImages($request, $product->all_images);
        }
        $product->update(array_merge($validated, ['images' => $images]));

        return redirect()->route('dashboard.products')->with('success', 'Product updated.');
    }

    public function destroyProduct(Product $product)
    {
        foreach ($product->all_images as $path) {
            $this->deleteStoredFile($path);
        }
        $name = $product->name;
        $product->delete();

        return redirect()->route('dashboard.products')->with('success', "Product \"{$name}\" deleted.");
    }

    // ── Orders ────────────────────────────────────────────────────────────────
    public function orders(Request $request)
    {
        $status = $request->input('status');
        $search = trim($request->input('search', ''));
        $userIdentifier = $request->input('user');
        $today = $request->boolean('today');
        $fulfillmentType = $request->input('type'); // delivery | pickup
        $month = $request->input('month');

        $query = Order::with(['user', 'items', 'location', 'deliveryProvider'])->orderBy('id', 'desc');

        if ($today) {
            $query->whereDate('created_at', today());
        }
        if ($month) {
            try {
                $m = Carbon::createFromFormat('Y-m', $month);
                $query->whereBetween('created_at', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()]);
            } catch (\Throwable $e) {
                // invalid month — ignore
            }
        }
        if ($userIdentifier) {
            $user = User::where('username', $userIdentifier)
                ->orWhere('email', $userIdentifier)
                ->first();
            if ($user) {
                $query->where('user_id', $user->id);
            }
        }
        if ($fulfillmentType) {
            $query->where('fulfillment_type', $fulfillmentType);
        }
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }
        if ($search !== '') {
            $query->where(
                fn($q) => $q
                    ->where('order_id', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($u) => $u->where('username', 'like', "%{$search}%"))
            );
        }

        $perPage = AdminSetting::int('dashboard_rows_per_page', 20);
        $orders = $query->paginate($perPage)->withQueryString();

        $baseQuery = Order::when($userIdentifier, function ($q) use ($userIdentifier) {
            $user = User::where('username', $userIdentifier)
                ->orWhere('email', $userIdentifier)
                ->first();
            if ($user) {
                $q->where('user_id', $user->id);
            }
        })->when($fulfillmentType, fn($q) => $q->where('fulfillment_type', $fulfillmentType))
          ->when($month, function ($q) use ($month) {
              try {
                  $m = Carbon::createFromFormat('Y-m', $month);
                  $q->whereBetween('created_at', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()]);
              } catch (\Throwable $e) {}
          });

        $statusCounts = $baseQuery
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')->pluck('total', 'status');

        $userFilter = User::where('username', $userIdentifier)
            ->orWhere('email', $userIdentifier)
            ->first();

        return view('dashboard.orders', compact('orders', 'statusCounts', 'status', 'search', 'userFilter', 'today', 'fulfillmentType', 'month'));
    }

    public function showOrder($order_id)
    {
        $order = Order::where('order_id', $order_id)->firstOrFail();
        $order->load(['user', 'items.product', 'location', 'deliveryProvider.zones']); // FIX [3]

        return view('dashboard.orders-show', compact('order'));
    }

    public function updateOrderStatus(Request $request, $order_id)
    {
        $order = Order::where('order_id', $order_id)->firstOrFail();
        $request->validate([
            'status'               => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'delivery_provider_id' => 'nullable|integer|exists:delivery_providers,id',
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // Assign a delivery provider when shipping (or delivering) a DELIVERY order.
        if (($newStatus === 'shipped' || $newStatus === 'delivered') && $request->filled('delivery_provider_id')) {
            $order->update(['delivery_provider_id' => (int) $request->input('delivery_provider_id')]);
        }

        // ── A DELIVERY order cannot be marked delivered without a provider ──
        // Consider a provider set either via this request OR already on the order.
        // Before the confirmation-prompt block so a provider is required for
        // both paths (Telegram tap-path and force-deliver fallback).
        if ($newStatus === 'delivered' && $order->isDelivery() && ! $request->filled('delivery_provider_id') && ! $order->delivery_provider_id) {
            return redirect()
                ->route('dashboard.orders.show', $order->order_id)
                ->with('error', 'Choose a delivery provider before marking this order delivered.');
        }

        // ── Delivery confirmation flow ────────────────────────────────────────
        // When staff/courier marks the DELIVERY RUN complete (shipped → delivered)
        // on a DELIVERY order, we do NOT flip straight to 'delivered'. Instead we
        // request the customer's confirmation in Telegram ("Confirm Received"),
        // keep the order at 'shipped', and only set 'delivered' once the customer
        // taps (confirmDeliveryFromTelegram). Fallback: if the customer has no
        // Telegram linked (or this is a pickup), proceed straight to delivered.
        $forceDeliver = (bool) $request->input('force_deliver');

        if (
            ! $forceDeliver
            && $oldStatus === 'shipped'
            && $newStatus === 'delivered'
            && ! $order->isPickup()
            && $order->user?->telegram_chat_id
            && ! $order->delivery_confirmed_at
        ) {
            try {
                app(TelegramBotService::class)->sendDeliveryConfirmationPrompt($order);
            } catch (\Throwable $e) {
                Log::warning('[Bot2] Delivery confirmation prompt failed, falling back to delivered: ' . $e->getMessage());
            }

            // If the prompt was sent, await the customer confirmation.
            if ($order->fresh()->delivery_confirm_requested_at) {
                return redirect()
                    ->route('dashboard.orders.show', $order->order_id)
                    ->with('success', 'Delivery marked complete — awaiting customer confirmation in Telegram.');
            }
            // Else (no chat delivered the prompt) fall through to normal delivered.
        }

        $updateData = ['status' => $newStatus];
        if ($newStatus === 'delivered' && !$order->delivery_confirmed_at) {
            $updateData['delivery_confirmed_at'] = now();
            $updateData['delivery_confirmed_by'] = 'staff';
        }
        $order->update($updateData);

        // ── Stock management: cancelling an order puts its stock back via the
        //    ledger. Reverses each not-yet-reversed movement referencing the order.
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            try {
                app(\App\Services\OrderStockService::class)->restoreOrderStock($order);
            } catch (\Throwable $e) {
                Log::warning('[Stock] Restore on cancel failed: ' . $e->getMessage());
            }
        }

        // ── Log activity ────────────────────────────────────────────────────────
        try {
            \App\Services\ActivityLogger::orderStatusChange($order, $oldStatus, $newStatus, $request);
        } catch (\Throwable $e) {
            Log::warning('[ActivityLog] Status update log failed: ' . $e->getMessage());
        }

        // Load relations needed by Telegram message builders
        $order->load(['items', 'user']);

        // ── Bot 1 — notify admin channel ──────────────────────────────────────
        $adminAlert = "📋 *Order Status Updated*\n\n" .
            "📦 Order: `#{$order->order_id}`\n" .
            "👤 " . ($order->user?->username ?? 'Guest') . "\n" .
            "🔄 {$oldStatus} → *{$newStatus}*\n";
        if ($newProvider = $order->getDeliveryProviderDetailsAttribute()) {
            $adminAlert .= "🚚 Provider: {$newProvider['name']}";
            if (! empty($newProvider['estimated_time'])) {
                $adminAlert .= " (ETA: {$newProvider['estimated_time']})";
            }
            $adminAlert .= "\n";
        }
        $adminAlert .= "🕐 " . now()->format('d M Y, H:i');

        try {
            app(TelegramService::class)->sendAlert($adminAlert);
        } catch (\Throwable $e) {
            Log::warning('[Bot1] Status alert failed: ' . $e->getMessage());
        }

        // ── Bot 2 — notify customer in their Telegram ─────────────────────────
        // Only fires if the customer has connected their Telegram account.
        try {
            $bot = app(TelegramBotService::class);
            match ($newStatus) {
                'confirmed' => $bot->onOrderConfirmed($order),
                'processing' => $bot->onOrderProcessing($order),
                'shipped' => $bot->onOrderShipped($order),
                'delivered' => $bot->onOrderDelivered($order),
                'cancelled' => $bot->onOrderCancelled($order),
                default => null, // 'pending' — no user alert
            };
        } catch (\Throwable $e) {
            Log::warning('[Bot2] User status notification failed: ' . $e->getMessage());
        }

        return redirect()->route('dashboard.orders.show', $order->order_id)->with('success', 'Order status updated to ' . strtoupper($newStatus) . '.');
    }

    public function confirmDelivery($order_id)
    {
        $order = Order::where('order_id', $order_id)->firstOrFail();
        if (!in_array($order->status, ['confirmed', 'processing', 'shipped'])) {
            return redirect()->route('dashboard.orders.show', $order->order_id)
                ->with('error', 'Order cannot be marked as delivered from its current status.');
        }

        $order->update(['status' => 'delivered', 'delivery_confirmed_at' => now()]);

        // ── Log activity ────────────────────────────────────────────────────────
        try {
            $me = auth('admin')->user() ?? auth('staff')->user();
            \App\Services\ActivityLogger::deliveryConfirmed($order, $me?->name ?? 'Admin', request());
        } catch (\Throwable $e) {
            Log::warning('[ActivityLog] Delivery confirm log failed: ' . $e->getMessage());
        }

        // Load relations needed by Telegram message builders
        $order->load(['items', 'user']);

        // ── Bot 1 — notify admin channel ──────────────────────────────────────
        try {
            app(TelegramService::class)->sendDeliveryConfirmed($order);
        } catch (\Throwable $e) {
            Log::warning('[Bot1] Delivery confirm failed: ' . $e->getMessage());
        }

        // ── Bot 2 — notify customer their order was delivered ─────────────────
        try {
            app(TelegramBotService::class)->onOrderDelivered($order);
        } catch (\Throwable $e) {
            Log::warning('[Bot2] User delivery notification failed: ' . $e->getMessage());
        }

        return redirect()->route('dashboard.orders.show', $order->order_id)
            ->with('success', "Order #{$order->order_id} marked as delivered ✅");
    }

    public function verifyOrderPayment($order_id)
    {
        $order = Order::where('order_id', $order_id)->firstOrFail();
        Log::info('Verify payment requested for order: ' . $order->order_id);

        // Update both payment status and order status
        $order->update([
            'payment_status' => 'paid',
            'status'         => $order->status === 'pending' ? 'confirmed' : $order->status
        ]);

        // ── Stock management: a pending (Bakong/KHQR) order was NOT stocked out
        //    at placement — stock leaves the shelf only on confirmation. Promote
        //    to confirmed → stock-out now (idempotent via the double-processing guard).
        if ($order->status === 'confirmed') {
            try {
                app(\App\Services\OrderStockService::class)->stockOutOrder($order, $order->items);
            } catch (\Throwable $e) {
                Log::warning('[Stock] Stock-out on payment verify failed: ' . $e->getMessage());
            }
        }

        // ── Log activity ────────────────────────────────────────────────────────
        try {
            \App\Services\ActivityLogger::paymentVerified($order, request());
        } catch (\Throwable $e) {
            Log::warning('[ActivityLog] Payment verify log failed: ' . $e->getMessage());
        }

        // Load relations needed by Telegram message builders
        $order->load(['items', 'user']);

        // ── Bot 1 — notify admin channel ──────────────────────────────────────
        try {
            app(TelegramService::class)->sendPaymentConfirmed($order, 'Manual Verification');
            Log::info('Admin Telegram receipt sent for manual verification ✅', ['order_id' => $order->id]);
        } catch (\Throwable $e) {
            Log::warning('Telegram admin receipt failed: ' . $e->getMessage());
        }

        // ── Bot 2 — notify customer in their Telegram ─────────────────────────
        try {
            app(TelegramUserService::class)->onPaymentConfirmed($order, 'Manual Verification');
            Log::info('Customer Telegram receipt sent for manual verification ✅', [
                'order_id'   => $order->id,
                'chat_id'    => $order->user?->telegram_chat_id ?? 'not connected',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Telegram customer receipt failed: ' . $e->getMessage());
        }

        return redirect()->route('dashboard.orders.show', $order->order_id)
            ->with('success', "Payment for Order #{$order->order_id} verified as paid ✅");
    }

    // ── Payments ──────────────────────────────────────────────────────────────
    // FIX [4]: payments() method added — referenced in layout.blade nav
    public function payments(Request $request)
    {
        $status = $request->input('status');
        $search = trim($request->input('search', ''));

        $query = Payment::with('order')->latest();

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }
        if ($search !== '') {
            $query->where(
                fn($q) => $q
                    ->whereHas('order', fn($o) => $o->where('order_id', 'like', "%{$search}%"))
                    ->orWhere('qr_md5', 'like', "%{$search}%")
                    ->orWhere('bakong_hash', 'like', "%{$search}%")
                    ->orWhere('from_account_id', 'like', "%{$search}%")
            );
        }

        $perPage = AdminSetting::int('dashboard_rows_per_page', 20);
        $payments = $query->paginate($perPage)->withQueryString();

        $statusCounts = Payment::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')->pluck('total', 'status');

        $stats = [
            'total_revenue' => Payment::where('status', 'paid')->sum('amount'),
            'paid' => Payment::where('status', 'paid')->count(),
            'pending' => Payment::where('status', 'pending')->count(),
            'expired' => Payment::where('status', 'expired')->count(),
            'failed' => Payment::where('status', 'failed')->count(),
            'manual_pending' => Payment::where('status', 'manual_pending')->count(),
            'refunded' => Payment::where('status', 'refunded')->count(),
            'paid_today' => Payment::where('status', 'paid')->whereDate('paid_at', today())->count(),
        ];

        return view('dashboard.payments', compact('payments', 'statusCounts', 'stats', 'status', 'search'));
    }

    // ── Users ─────────────────────────────────────────────────────────────────
    public function users(Request $request)
    {
        // Build base query — include total_spent via subquery for VIP progress bar in blade
        $query = User::withCount('orders')
            ->selectRaw(
                'users.*, COALESCE((SELECT SUM(total) FROM orders WHERE orders.user_id = users.id AND orders.status != ?), 0) as total_spent',
                ['cancelled']
            )
            ->latest();

        // Role filter
        if ($role = $request->input('role')) {
            if ($role !== 'all') {
                $query->where('role', $role);
            }
        }

        // Telegram filter
        if ($request->input('telegram') === 'connected') {
            $query->whereNotNull('telegram_chat_id');
        }

        // Search
        if ($search = trim($request->input('search', ''))) {
            $query->where(
                fn($q) => $q
                    ->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
            );
        }

        // "Recently logged-in" filter: ?recent=1 → only users who have logged in
        // (last_login_at set), newest login first. The dashboard "new customers"
        // stat opens this view.
        $recent = $request->boolean('recent');
        if ($recent) {
            $query->whereNotNull('last_login_at')->orderByDesc('last_login_at');
        }

        $perPage = AdminSetting::int('dashboard_rows_per_page', 15);
        $users = $query->paginate($perPage)->withQueryString();

        $roleCounts = User::selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role')
            ->toArray();

        $vipGoal = (float) AdminSetting::get('vip_threshold', 5000);

        return view('dashboard.users', compact('users', 'roleCounts', 'vipGoal', 'recent'));
    }

    // ── Banners ───────────────────────────────────────────────────────────────
    public function banners()
    {
        $banners = Banner::orderBy('order')->get();

        return view('dashboard.banners', compact('banners'));
    }

    // ── Discounts ─────────────────────────────────────────────────────────────
    public function discounts()
    {
        $discounts = \App\Models\Discount::latest()->get();
        $products  = \App\Models\Product::all();

        // Dynamic category groups for the "applies to categories" chip picker —
        // derived from the navigation tree (main category → sub categories).
        $categoryGroups = \App\Services\CategoryFilterOptions::treeGroups();

        return view('dashboard.discounts', compact('discounts', 'products', 'categoryGroups'));
    }

    public function discountsStore(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:discounts,code',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'product_id' => 'nullable|exists:products,id',
            'categories' => 'nullable|array',
            'categories.*' => 'string|max:100',
            'kind' => 'nullable|in:code,badge', // Ensure kind is validated
        ]);
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->has('is_active');
        $data['kind'] = $request->input('kind', 'code'); // Handle kind
        $data['product_id'] = $request->input('product_id') ?: null;
        $data['min_order'] = $request->input('min_order') ?: 0;
        $data['max_uses'] = $request->input('max_uses') ?: null;
        $data['categories'] = $data['product_id'] ? null : ($request->input('categories', []) ?: null);

        \App\Models\Discount::create($data);

        return redirect()->route('dashboard.discounts')->with('success', 'Discount created.');
    }

    public function discountsUpdate(Request $request, Discount $discount)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:discounts,code,' . $discount->id,
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'product_id' => 'nullable|exists:products,id',
            'categories' => 'nullable|array',
            'categories.*' => 'string|max:100',
            'kind' => 'nullable|in:code,badge', // Ensure kind is validated
        ]);
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->has('is_active');
        $data['kind'] = $request->input('kind', $discount->kind ?? 'code'); // Handle kind
        $data['product_id'] = $request->input('product_id') ?: null;
        $data['min_order'] = $request->input('min_order') ?: 0;
        $data['max_uses'] = $request->input('max_uses') ?: null;
        $data['categories'] = $data['product_id'] ? null : ($request->input('categories', []) ?: null);

        $discount->update($data);

        return redirect()->route('dashboard.discounts')->with('success', 'Discount updated.');
    }

    public function discountsSaveBadge(Request $request, Discount $discount)
    {
        // clear_badge=1 means the admin clicked "CLEAR BADGE"
        if ($request->boolean('clear_badge')) {
            $discount->update([
                'badge_config' => null,
                // Reset kind to 'code' when badge is cleared
                'kind'         => 'code',
            ]);
            return response()->json(['success' => true, 'message' => 'Badge cleared.']);
        }

        $request->validate([
            'badge_config' => 'required|array',
            'badge_config.text' => 'required|string|max:30',
            'badge_config.icon' => 'nullable|string|max:10',
            'badge_config.bg' => 'nullable|string|max:100',
            'badge_config.border' => 'nullable|string|max:100',
            'badge_config.color' => 'nullable|string|max:30',
        ]);

        $discount->update([
            'badge_config' => $request->input('badge_config'),
        ]);

        return response()->json(['success' => true, 'message' => 'Badge saved.']);
    }

    public function discountsDestroy(Discount $discount)
    {
        $discount->delete();

        return redirect()->route('dashboard.discounts')->with('success', 'Discount deleted.');
    }

    // ── Full Dashboard Export (all sheets) ───────────────────────────────────
    // Route: GET /dashboard/export
    // Params: from (Y-m-d), to (Y-m-d), format (xlsx|csv)
    //
    // Produces a multi-sheet .xlsx covering:
    //   Summary | Monthly Sales | Daily Sales | Orders |
    //   Order Status | Category Revenue | Top Products | Discounts
    public function dashboardExport(Request $request)
    {
        // ── 1. Parse & validate the month inputs (blade sends Y-m) ────────────────
        $fromRaw = $request->input('from', \Carbon\Carbon::now()->subMonth()->format('Y-m'));
        $toRaw = $request->input('to', \Carbon\Carbon::now()->format('Y-m'));

        // Validate format: must be Y-m (e.g. "2025-01")
        $monthPattern = '/^\d{4}-(0[1-9]|1[0-2])$/';
        if (!preg_match($monthPattern, $fromRaw) || !preg_match($monthPattern, $toRaw)) {
            return back()->withErrors(['export' => 'Invalid date format. Please use month pickers.']);
        }

        // Convert Y-m → full Y-m-d dates for the export class
        // (append '-01' so the day is explicit — see FIX in index())
        $from = \Carbon\Carbon::createFromFormat('Y-m-d', $fromRaw . '-01')->startOfMonth()->format('Y-m-d');
        $to = \Carbon\Carbon::createFromFormat('Y-m-d', $toRaw . '-01')->endOfMonth()->format('Y-m-d');

        // Ensure "from" is not after "to"
        if ($from > $to) {
            return back()->withErrors(['export' => '"From" month cannot be after "To" month.']);
        }

        // ── 2. Determine export format ────────────────────────────────────────────
        $format = in_array($request->input('format'), ['xlsx', 'csv', 'pdf']) ? $request->input('format') : 'xlsx';
        $filename = 'dashboard-' . $from . '-to-' . $to . '.' . $format;

        // ── 3. Stream the download ────────────────────────────────────────────────
        if ($format === 'pdf') {
            return (new \App\Exports\PdfExport(
                \Carbon\Carbon::parse($from)->startOfDay(),
                \Carbon\Carbon::parse($to)->endOfDay()
            ))->download();
        }

        if ($format === 'csv') {
            // CSV does not support multiple sheets or styling.
            // Use dedicated CsvExport class for clean tabular data only.
            return Excel::download(
                new \App\Exports\CsvExport(
                    \Carbon\Carbon::parse($from)->startOfDay(),
                    \Carbon\Carbon::parse($to)->endOfDay()
                ),
                $filename,
                \Maatwebsite\Excel\Excel::CSV,
                ['Content-Type' => 'text/csv']
            );
        }

        // Default: full multi-sheet .xlsx
        return Excel::download(new DashboardExport($from, $to), $filename);
    }

    // ── Private helpers ───────────────────────────────────────────────────────
    private function processImages(Request $request, array $currentImages): array
    {
        $finalImages = [];

        foreach ($request->input('existing_images', []) as $path) {
            $clean = $this->normalizeImagePath($path);
            if ($clean && !in_array($clean, $finalImages)) {
                $finalImages[] = $clean;
            }
        }

        $removedPaths = array_diff($currentImages, $request->input('existing_images', []));
        foreach ($removedPaths as $removed) {
            $this->deleteStoredFile($removed);
        }

        if ($request->hasFile('image_files')) {
            foreach ($request->file('image_files') as $file) {
                if (!$file->isValid() || count($finalImages) >= 8) {
                    continue;
                }
                // FIX: Upload to S3/R2 (production) or local (dev)
                $url = $this->storeFile($file, 'products');
                if ($url) {
                    $finalImages[] = $url;
                }
            }
        }

        foreach ($request->input('image_urls', []) as $url) {
            if (count($finalImages) >= 8) {
                break;
            }
            $url = trim($url);
            if (filter_var($url, FILTER_VALIDATE_URL) && !in_array($url, $finalImages)) {
                $finalImages[] = $url;
            }
        }

        return array_values(array_slice($finalImages, 0, 8));
    }

    private function normalizeImagePath(string $path): ?string
    {
        $path = trim($path);
        if (empty($path)) {
            return null;
        }

        if (Str::startsWith($path, ['https://', 'http://'])) {
            $appUrl = rtrim(config('app.url'), '/');
            foreach (array_unique([$appUrl, 'http://127.0.0.1:8000', 'http://localhost:8000', 'http://localhost']) as $base) {
                if ($base && Str::startsWith($path, $base . '/')) {
                    return '/' . ltrim(substr($path, strlen($base)), '/');
                }
            }

            return $path;
        }

        return '/' . ltrim($path, '/');
    }

    private function deleteStoredFile(string $path): void
    {
        $this->deleteStorageFile($path);
    }

    private function dateFormatExpr(string $column, string $format): string
    {
        $driver = DB::getDriverName();
        $sqlFormat = str_replace(['Y', 'm', 'd'], ['%Y', '%m', '%d'], $format);

        return match ($driver) {
            'pgsql' => "TO_CHAR({$column}, '" . str_replace(['%Y', '%m', '%d'], ['YYYY', 'MM', 'DD'], $sqlFormat) . "')",
            'sqlite' => "strftime('{$sqlFormat}', {$column})",
            default => "DATE_FORMAT({$column}, '{$sqlFormat}')",
        };
    }

    public function stats()
    {
        $now = Carbon::now();
        $prev = Carbon::now()->subMonth();

        $current = $this->buildDashboardData()['stats'];
        return response()->json([
            'total_users'    => $current['total_users'],
            'total_orders'   => $current['total_orders'],
            'total_revenue'  => $current['total_revenue'],
            'total_products' => $current['total_products'],
            'prev_users'     => (int) ($current['total_users'] * 0.95), // Example logic
            'prev_orders'    => (int) ($current['total_orders'] * 0.98),
            'prev_revenue'   => (float) ($current['total_revenue'] * 0.92),
        ]);
    }
}