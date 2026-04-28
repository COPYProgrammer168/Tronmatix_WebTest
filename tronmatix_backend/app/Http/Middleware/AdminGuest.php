<?php

// app/Http/Middleware/AdminGuest.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * AdminGuest
 *
 * Prevents already-authenticated dashboard users from reaching the login/register
 * pages. Checks BOTH the 'admin' guard AND the 'staff' guard — without this,
 * a staff member logged in under the 'staff' guard could still see the login page
 * because the original middleware only checked Auth::guard('admin')->check().
 */
class AdminGuest
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('admin')->check() || Auth::guard('staff')->check()) {
            return redirect()->route('dashboard.index');
        }

        return $next($request);
    }
}
