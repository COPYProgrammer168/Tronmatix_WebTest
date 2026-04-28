<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * StaffAuthenticate
 *
 * Protects routes that staff members (editor, seller, delivery, developer)
 * can reach. Checks the 'staff' guard.
 *
 * If a staff user's account is deactivated by an admin, they are immediately
 * kicked out on their next request.
 */
class StaffAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('staff')->check()) {
            return redirect()->route('dashboard.login')
                ->with('error', 'Please login to access the dashboard.');
        }

        /** @var \App\Models\Staff $staff */
        $staff = Auth::guard('staff')->user();

        // Deactivated accounts are logged out immediately
        if (! $staff->is_active) {
            Auth::guard('staff')->logout();

            return redirect()->route('dashboard.login')
                ->with('error', 'Your account has been deactivated. Contact an administrator.');
        }

        return $next($request);
    }
}
