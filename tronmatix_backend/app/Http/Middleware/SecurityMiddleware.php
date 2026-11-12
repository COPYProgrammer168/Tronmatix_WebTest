<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecurityMiddleware
 *
 * Covers BOTH the web guard (regular users) and the admin guard.
 * Protects against session hijacking via fingerprint binding,
 * absolute timeout enforcement, and periodic session ID rotation.
 *
 * Register in Kernel.php under the 'web' and 'admin' middleware groups.
 */
class SecurityMiddleware
{
    /**
     * Guards to check. Both use session drivers, so both need fingerprinting.
     * If you add more session-based guards later, add them here.
     */
    protected array $guards = ['web', 'admin'];

    /**
     * Routes that skip fingerprint checks (unauthenticated entry points).
     */
    protected array $except = [
        'login',
        'register',
        'password/reset',
        'dashboard/login', // admin login route
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->inExceptArray($request)) {
            return $next($request);
        }

        foreach ($this->guards as $guard) {
            if (! Auth::guard($guard)->check()) {
                continue; // this guard has no active session — skip it
            }

            // Use a guard-namespaced session key so web and admin fingerprints
            // never collide (e.g. _fingerprint_web vs _fingerprint_admin)
            if (! $this->validateSessionFingerprint($request, $guard)) {
                return $this->terminateSession($request, $guard, 'Session fingerprint mismatch.');
            }

            if ($this->isSessionExpired($guard)) {
                return $this->terminateSession($request, $guard, 'Absolute session timeout reached.');
            }

            $this->rotateSessionIfDue($request, $guard);
        }

        return $next($request);
    }

    // -------------------------------------------------------------------------
    // Fingerprint
    // -------------------------------------------------------------------------

    protected function buildFingerprint(Request $request): string
    {
        $ua   = $request->header('User-Agent', '');
        $ip   = $request->ip();
        $lang = $request->header('Accept-Language', '');
        $key  = config('app.key'); // HMAC secret — never exposed in responses

        return hash_hmac('sha256', $ua . '|' . $ip . '|' . $lang, $key);
    }

    /**
     * Namespaced session key so each guard gets its own fingerprint slot.
     */
    protected function fingerprintKey(string $guard): string
    {
        return "_fingerprint_{$guard}";
    }

    protected function startedAtKey(string $guard): string
    {
        return "_session_started_at_{$guard}";
    }

    protected function rotatedAtKey(string $guard): string
    {
        return "_last_rotated_at_{$guard}";
    }

    /**
     * Bind fingerprint + timestamps for a specific guard.
     * Called on login — pass the guard name explicitly.
     */
    public static function bindSessionFingerprint(Request $request, string $guard = 'web'): void
    {
        $instance = new self();
        Session::put($instance->fingerprintKey($guard), $instance->buildFingerprint($request));
        Session::put($instance->startedAtKey($guard), now()->timestamp);
        Session::put($instance->rotatedAtKey($guard), now()->timestamp);
    }

    protected function validateSessionFingerprint(Request $request, string $guard): bool
    {
        $stored = Session::get($this->fingerprintKey($guard));

        if (is_null($stored)) {
            // First request after login — bind now and allow through
            self::bindSessionFingerprint($request, $guard);
            return true;
        }

        return hash_equals($stored, $this->buildFingerprint($request));
    }

    // -------------------------------------------------------------------------
    // Lifetime
    // -------------------------------------------------------------------------

    protected function isSessionExpired(string $guard): bool
    {
        $startedAt = Session::get($this->startedAtKey($guard));
        if (! $startedAt) {
            return false;
        }

        $max = config('session.absolute_timeout', 28800); // 8 hours default

        return (now()->timestamp - $startedAt) > $max;
    }

    protected function rotateSessionIfDue(Request $request, string $guard): void
    {
        $lastRotated = Session::get($this->rotatedAtKey($guard), 0);
        $interval    = config('session.rotation_interval', 900); // 15 min default

        if ((now()->timestamp - $lastRotated) >= $interval) {
            $request->session()->regenerate(); // new ID, data preserved
            Session::put($this->rotatedAtKey($guard), now()->timestamp);
        }
    }

    // -------------------------------------------------------------------------
    // Termination
    // -------------------------------------------------------------------------

    protected function terminateSession(Request $request, string $guard, string $reason): Response
    {
        Log::warning('Security: session terminated', [
            'guard'  => $guard,
            'reason' => $reason,
            'ip'     => $request->ip(),
            'ua'     => substr($request->header('User-Agent', ''), 0, 100),
            'user'   => Auth::guard($guard)->id(),
            'url'    => $request->url(),
        ]);

        Auth::guard($guard)->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Session expired or invalid. Please log in again.',
                'code'    => 'SESSION_TERMINATED',
            ], 401);
        }

        // Route back to the appropriate login page based on guard
        $loginRoute = $guard === 'admin' ? 'dashboard.login' : 'login';

        return redirect()->route($loginRoute)->withErrors([
            'session' => 'Your session expired or was invalid. Please log in again.',
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function inExceptArray(Request $request): bool
    {
        foreach ($this->except as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }
        return false;
    }
}