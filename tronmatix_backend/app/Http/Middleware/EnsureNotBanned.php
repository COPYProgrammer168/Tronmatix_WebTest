<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && method_exists($user, 'isBanned') && $user->isBanned()) {
            // For API requests
            if ($request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Your account has been banned.'], 403);
            }

            // For web requests
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/login')->withErrors(['username' => 'Your account has been banned.']);
        }

        return $next($request);
    }
}
