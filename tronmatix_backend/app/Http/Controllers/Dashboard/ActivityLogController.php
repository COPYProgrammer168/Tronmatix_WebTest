<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ActivityLogController extends Controller
{
    private function me()
    {
        // Try session guards first (web dashboard), then Sanctum token (React dashboard)
        $user = Auth::guard('admin')->user() ?? Auth::guard('staff')->user() ?? request()->user();
        if (! $user) {
            abort(401, 'Unauthorized');
        }
        return $user;
    }

    private function assertAdmin(): void
    {
        $user = $this->me();
        $role = $user->role ?? '';
        abort_unless(
            in_array($role, ['admin', 'superadmin']),
            403,
            'Access denied.'
        );
    }

    /**
     * Show the activity log page (blade view).
     */
    public function show(Request $request)
    {
        $this->assertAdmin();

        $query = ActivityLog::query()->whereNotNull('created_at');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }
        if ($request->filled('actor_name')) {
            $query->where('actor_name', 'like', '%' . $request->actor_name . '%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('include_logins') && $request->include_logins === '0') {
            $query->where('action', '!=', 'login_failed');
            $query->where('action', '!=', 'login_rate_limited');
        }

        $perPage = min((int) $request->input('per_page', 50), 100);
        $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $actorNames = Cache::remember('activity_log_actor_names', 3600, function () {
            return ActivityLog::select('actor_name')
                ->distinct()
                ->whereNotNull('actor_name')
                ->orderBy('actor_name')
                ->pluck('actor_name');
        });

        $entityTypes = Cache::remember('activity_log_entity_types', 3600, function () {
            return ActivityLog::select('entity_type')
                ->distinct()
                ->whereNotNull('entity_type')
                ->orderBy('entity_type')
                ->pluck('entity_type');
        });

        $actions = Cache::remember('activity_log_actions', 3600, function () {
            return ActivityLog::select('action')
                ->distinct()
                ->orderBy('action')
                ->pluck('action');
        });

        // Recent critical alerts — order status updates & login events (last 24h)
        $recentAlerts = ActivityLog::whereIn('action', [
                'order_status_update', 'order_cancelled',
                'login_success', 'login_failed',
                'payment_verified', 'delivery_confirmed',
            ])
            ->where('created_at', '>=', now()->subHours(24))
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('dashboard.activity-log', compact('logs', 'actorNames', 'entityTypes', 'actions', 'recentAlerts'));
    }

    /**
     * List activity logs with optional filters.
     */
    public function index(Request $request)
    {
        $this->assertAdmin();

        $query = ActivityLog::query()->whereNotNull('created_at');

        // Filters
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->filled('actor_id')) {
            $query->where('actor_id', $request->actor_id);
        }

        if ($request->filled('actor_name')) {
            $query->where('actor_name', 'like', '%' . $request->actor_name . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Exclude login_failed noise when not filtering for it
        if ($request->filled('include_logins') && $request->include_logins === '0') {
            $query->where('action', '!=', 'login_failed');
            $query->where('action', '!=', 'login_rate_limited');
        }

        $perPage = min((int) $request->input('per_page', 50), 500);

        $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $logs->items(),
            'meta'    => [
                'total'        => $logs->total(),
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'per_page'     => $logs->perPage(),
            ],
        ]);
    }

    /**
     * Summary stats for the dashboard.
     */
    public function stats(Request $request)
    {
        $this->assertAdmin();

        $today = now()->startOfDay();

        return response()->json([
            'success' => true,
            'data'    => [
                'total_today'         => ActivityLog::whereDate('created_at', today())->count(),
                'login_failures_today'=> ActivityLog::where('action', 'login_failed')
                                        ->whereDate('created_at', today())->count(),
                'rate_limits_today'   => ActivityLog::where('action', 'login_rate_limited')
                                        ->whereDate('created_at', today())->count(),
                'recent_actions'      => ActivityLog::where('created_at', '>=', $today)
                                        ->orderBy('created_at', 'desc')
                                        ->limit(10)
                                        ->get(),
            ],
        ]);
    }
}