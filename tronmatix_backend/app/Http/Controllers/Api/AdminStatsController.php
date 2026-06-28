<?php

// ════════════════════════════════════════════════════════════════════════════
// app/Http/Controllers/Api/AdminStatsController.php
// GET /api/admin/stats   → OverviewTab stats
// GET /api/admin/users   → UsersTab / DevDashboard UsersTab
// ════════════════════════════════════════════════════════════════════════════

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStatsController extends Controller
{
    /** GET /api/admin/stats */
    public function stats()
    {
        $now       = now();
        $thisMonth = $now->copy()->startOfMonth();
        $lastMonth = $now->copy()->subMonth()->startOfMonth();
        $lastEnd   = $now->copy()->subMonth()->endOfMonth();

        $totalOrders   = Order::count();
        $lastOrders    = Order::whereBetween('created_at', [$lastMonth, $lastEnd])->count();
        $thisOrders    = Order::where('created_at', '>=', $thisMonth)->count();

        $revenue       = Order::whereNotIn('status', ['cancelled'])->sum('total_price');
        $lastRevenue   = Order::whereNotIn('status', ['cancelled'])->whereBetween('created_at', [$lastMonth, $lastEnd])->sum('total_price');
        $thisRevenue   = Order::whereNotIn('status', ['cancelled'])->where('created_at', '>=', $thisMonth)->sum('total_price');

        $activeUsers   = User::where('role', 'customer')->count();
        $pendingOrders = Order::where('status', 'pending')->count();

        // Weekly orders — last 7 days
        $weeklyOrders = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            return [
                'day'   => $date->format('D'),
                'count' => Order::whereDate('created_at', $date->toDateString())->count(),
            ];
        });

        return response()->json([
            'total_orders'   => number_format($totalOrders),
            'revenue'        => '$' . number_format($revenue, 0),
            'active_users'   => number_format($activeUsers),
            'pending_orders' => $pendingOrders,

            'orders_delta'  => $this->delta($thisOrders, $lastOrders),
            'revenue_delta' => $this->delta($thisRevenue, $lastRevenue),
            'users_delta'   => null,
            'pending_delta' => null,

            'weekly_orders' => $weeklyOrders,
        ]);
    }

    /** GET /api/admin/users */
    public function users()
    {
        $users = User::withCount('orders')
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'username', 'email', 'role', 'email_verified_at', 'created_at']);

        return response()->json($users);
    }

    private function delta($current, $previous): ?string
    {
        if ($previous == 0) return null;
        $pct = round((($current - $previous) / $previous) * 100);
        return ($pct >= 0 ? '+' : '') . $pct . '%';
    }
}
