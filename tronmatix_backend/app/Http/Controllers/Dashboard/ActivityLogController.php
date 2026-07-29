<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $query = ActivityLog::query();

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

        $perPage = min((int) $request->input('per_page', 50), 200);
        $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $actorNames = ActivityLog::select('actor_name')
            ->distinct()
            ->whereNotNull('actor_name')
            ->orderBy('actor_name')
            ->pluck('actor_name');

        $entityTypes = ActivityLog::select('entity_type')
            ->distinct()
            ->whereNotNull('entity_type')
            ->orderBy('entity_type')
            ->pluck('entity_type');

        $actions = ActivityLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('dashboard.activity-log', compact('logs', 'actorNames', 'entityTypes', 'actions'));
    }

    /**
     * List activity logs with optional filters.
     */
    public function index(Request $request)
    {
        $this->assertAdmin();

        $query = ActivityLog::query()->withCount([]);

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

        $perPage = min((int) $request->input('per_page', 50), 200);

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