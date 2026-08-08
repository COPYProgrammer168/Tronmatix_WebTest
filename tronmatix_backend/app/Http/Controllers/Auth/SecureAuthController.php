<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SecurityMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

/**
 * SecureAuthController
 *
 * Session-based auth for the web guard (SPA).
 * Handles: rate limiting, session fixation protection,
 * fingerprint binding, CSRF token rotation.
 */
class SecureAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email:rfc', 'max:254'],
            // Min:8 enforced here for the guard — the actual stored hash already
            // requires 8+ from registration. This just avoids wasted DB lookups.
            'password' => ['required', 'string', 'min:8', 'max:128'],
        ]);

        $throttleKey = $this->throttleKey($request);
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => ["Too many login attempts. Please try again in {$seconds} seconds."],
            ]);
        }

        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials, false)) {
            RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password.'],
            ]);
        }

        RateLimiter::clear($throttleKey);

        // Regenerate session ID — prevents session fixation
        $request->session()->regenerate();

        // Bind fingerprint (IP + UA HMAC) to this session
        SecurityMiddleware::bindSessionFingerprint($request);

        // Issue a fresh CSRF token for the new session
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Login successful.',
            'user'    => [
                'id'    => Auth::id(),
                'name'  => Auth::user()->name,
                'email' => Auth::user()->email,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    protected function throttleKey(Request $request): string
    {
        return Str::lower($request->input('email', '')) . '|' . $request->ip();
    }
}