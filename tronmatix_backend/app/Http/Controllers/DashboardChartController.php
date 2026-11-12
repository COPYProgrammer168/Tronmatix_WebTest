<?php

// app/Http/Controllers/DashboardChartController.php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Drill-down detail page for the six dashboard index charts.
 *
 * Each chart card on /dashboard wraps in a link to /dashboard/charts/{chart}.
 * This controller reuses the same aggregation SQL as DashboardController's
 * buildDashboardData() (via the inherited rangeWindow()/chartData() helpers) so
 * the numbers match the main dashboard, and adds a Today / Month / 6 Months /
 * Year range toggle plus a Recent Orders table scoped to the same range.
 */
class DashboardChartController extends DashboardController
{
    /** Route whitelist mirroring the ->whereIn() on web.php. */
    private const CHARTS = ['revenue', 'orders', 'sales', 'users', 'status', 'category'];

    /** Allowed range values. */
    private const RANGES = ['today', 'month', '6-months', 'this-year'];

    private const CHART_TITLES = [
        'revenue'  => 'dashboard.charts.monthlyRevenue',
        'orders'   => 'dashboard.charts.monthlyOrders',
        'sales'    => 'dashboard.charts.dailySales',
        'users'    => 'dashboard.charts.userRegistrations',
        'status'   => 'dashboard.charts.orderStatus',
        'category' => 'dashboard.charts.revenueByCategory',
    ];

    public function show(Request $request, string $chart)
    {
        // Route whereIn() already guards this, but defend against a direct call.
        if (! in_array($chart, self::CHARTS, true)) {
            abort(404);
        }

        $range = $request->get('range', 'month');
        if (! in_array($range, self::RANGES, true)) {
            $range = 'month';
        }

        // Chart label/value arrays — same SQL as the main dashboard.
        $data = $this->chartData($chart, $range);
        $labels = $data['labels'];
        $values = $data['values'];

        // Recent orders scoped to the selected range (same card columns as the
        // dashboard index Recent Orders table), paginated.
        [$start, $end] = $this->rangeWindow($range);
        $recent_orders = Order::with(['user', 'items', 'location'])
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->paginate(15)
            ->onEachSide(1)
            ->withQueryString();

        // The User Registrations page shows a "Recently Logged In" table instead
        // of Recent Orders — users whose last_login_at falls in the range.
        $recent_users = null;
        if ($chart === 'users') {
            $recent_users = \App\Models\User::withCount('orders')
                ->whereNotNull('last_login_at')
                ->whereBetween('last_login_at', [$start, $end])
                ->orderByDesc('last_login_at')
                ->paginate(15)
                ->onEachSide(1)
                ->withQueryString();
        }

        $chartTitle = __(self::CHART_TITLES[$chart]);

        $rangeNames = [
            'today'     => 'Today',
            'month'     => 'Month',
            '6-months'  => '6 Months',
            'this-year' => 'Year',
        ];

        return view('dashboard.chart-show', compact(
            'chart', 'chartTitle', 'range', 'rangeNames', 'labels', 'values',
            'recent_orders', 'recent_users'
        ));
    }
}