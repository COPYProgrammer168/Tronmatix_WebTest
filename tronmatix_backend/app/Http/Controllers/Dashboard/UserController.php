<?php

// app/Http/Controllers/Dashboard/UserController.php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    // ── GET /dashboard/users ──────────────────────────────────────────────────
    public function index(Request $request): View
    {
        $query = User::withCount('orders')
            ->withSum(['orders as total_spent' => function ($q) {
                $q->whereNotIn('status', ['cancelled']);
            }], 'total')
            ->latest();

        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $term = '%'.$request->search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('username', 'LIKE', $term)
                    ->orWhere('name', 'LIKE', $term)
                    ->orWhere('email', 'LIKE', $term);
            });
        }

        $users = $query->paginate(15)->withQueryString();

        $roleCounts = User::selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role')
            ->toArray();

        return view('dashboard.users', compact('users', 'roleCounts'));
    }

    // ── PUT /dashboard/users/{user}/role ──────────────────────────────────────
    // Supports both AJAX (returns JSON) and standard form POST (redirects back).
    public function updateRole(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $request->validate([
            'role' => 'required|in:'.implode(',', User::ROLES),
        ]);

        $newRole = $request->role;
        $oldRole = $user->role ?? 'customer';

        $user->update([
            'role' => $newRole,
            'is_banned' => $newRole === 'banned',
        ]);

        $label = User::ROLE_LABELS[$newRole] ?? $newRole;
        $message = "@{$user->username} role updated to {$label}.";

        // AJAX request — return JSON so the blade JS can update the badge in-place
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'role' => $newRole,
                'label' => $label,
            ]);
        }

        // Standard form POST fallback
        return back()->with('success', $message);
    }
}
