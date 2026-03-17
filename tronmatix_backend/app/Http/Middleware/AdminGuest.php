<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminGuest
{
    public function handle(Request $request, Closure $next): Response
    {
        // If already authenticated as admin, no need to see login/register
        if (Auth::guard('admin')->check()) {
            return redirect()->route('dashboard.index');
        }

        return $next($request);
    }
}
