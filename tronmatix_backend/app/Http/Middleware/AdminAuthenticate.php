<?php

// app/Http/Middleware/AdminAuthenticate.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * AdminAuthenticate
 *
 * Protects all dashboard routes.
 * Accepts users authenticated under EITHER the 'admin' guard (Admin model)
 * OR the 'staff' guard (Staff model).
 *
 * Priority: admin guard checked first, then staff guard.
 * Deactivated accounts under either guard are kicked out immediately.
 */
class AdminAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        // ── 1. Admin guard (superadmin / admin) ───────────────────────────────
        if (Auth::guard('admin')->check()) {
            /** @var \App\Models\Admin $admin */
            $admin = Auth::guard('admin')->user();

            if (! $admin->is_active) {
                Auth::guard('admin')->logout();
                return redirect()->route('dashboard.login')
                    ->with('error', 'Your account has been deactivated.');
            }

            return $next($request);
        }

        // ── 2. Staff guard (editor / seller / delivery / developer) ───────────
        if (Auth::guard('staff')->check()) {
            /** @var \App\Models\Staff $staff */
            $staff = Auth::guard('staff')->user();

            if (! $staff->is_active) {
                Auth::guard('staff')->logout();
                return redirect()->route('dashboard.login')
                    ->with('error', 'Your account has been deactivated. Contact an administrator.');
            }

            return $next($request);
        }

        // ── 3. Not authenticated ──────────────────────────────────────────────
        return redirect()->route('dashboard.login')
            ->with('error', 'Please login to access the dashboard.');
    }
}
