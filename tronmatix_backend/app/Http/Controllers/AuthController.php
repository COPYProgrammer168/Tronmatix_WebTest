<?php

// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ── Register ──────────────────────────────────────────────────────────────
    public function register(Request $request)
    {
        // Normalize before validation so uniqueness check is case-insensitive
        $request->merge([
            'username' => strtolower(trim($request->input('username', ''))),
            'email'    => strtolower(trim($request->input('email', ''))),
        ]);

        $validated = $request->validate([
            'username' => 'required|string|min:3|max:50|unique:users,username|regex:/^[a-zA-Z0-9_]+$/',
            // 'email:rfc,dns' does a live DNS MX lookup — disable with
            // VALIDATE_EMAIL_DNS=false in .env if you want to avoid external calls.
            'email'    => 'required|email:' . (env('VALIDATE_EMAIL_DNS', true) ? 'rfc,dns' : 'rfc') . '|max:255|unique:users,email',
            'password' => $this->passwordRules(),
        ], [
            'password.min'        => 'Password must be at least 8 characters.',
            'password.mixed_case' => 'Password must contain at least one uppercase and one lowercase letter.',
            'password.numbers'    => 'Password must contain at least one number.',
            'password.symbols'    => 'Password must contain at least one special character.',
            'username.regex'      => 'Username may only contain letters, numbers, and underscores.',
        ]);

        $user = User::create([
            'name'     => $validated['username'],
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'customer',
        ]);

        // Do NOT return a token on register — user must explicitly log in.
        // This prevents auto-auth with a token the user didn't knowingly request.
        return response()->json([
            'success' => true,
            'message' => 'Account created successfully. Please log in.',
            'user'    => [
                'id'       => $user->id,
                'username' => $user->username,
                'email'    => $user->email,
            ],
        ], 201);
    }

    // ── Login ─────────────────────────────────────────────────────────────────
    public function login(Request $request)
    {
        // Rate limit: 5 attempts per identifier+IP per minute
        $throttleKey = $this->throttleKey($request);
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            Log::channel('security')->warning('Auth: rate limit hit', [
                'ip'    => $request->ip(),
                'input' => Str::lower($request->input('username', '')),
            ]);

            throw ValidationException::withMessages([
                'username' => ["Too many login attempts. Please try again in {$seconds} seconds."],
            ]);
        }

        $validated = $request->validate([
            'username' => 'required|string|max:254',
            'password' => 'required|string|max:128',
        ]);

        // Normalize — handles mobile autocap and trailing spaces
        $input = strtolower(trim($validated['username']));

        // Case-insensitive lookup by username OR email
        $user = User::whereRaw('LOWER(username) = ?', [$input])
            ->orWhereRaw('LOWER(email) = ?', [$input])
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            Log::channel('security')->notice('Auth: failed login attempt', [
                'ip'    => $request->ip(),
                'input' => $input,
            ]);

            // Generic message — never reveal which field was wrong
            throw ValidationException::withMessages([
                'username' => ['Invalid username/email or password.'],
            ]);
        }

        // FIX: Check ban BEFORE clearing the rate limiter.
        // Previously, a banned user who knew their password would clear the
        // limiter on every attempt, bypassing brute-force protection entirely.
        if ($user->is_banned) {
            // Still count this as a hit so ban-testing is rate limited too
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'username' => ['Your account has been suspended. Contact support.'],
            ]);
        }

        // Credentials valid + not banned — safe to clear the limiter now
        RateLimiter::clear($throttleKey);

        // Revoke all old tokens before issuing a new one (one active session at a time)
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'user'    => $this->userPayload($user),
            'token'   => $token,
        ]);
    }

    // ── Logout ────────────────────────────────────────────────────────────────
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true]);
    }

    // ── Me ────────────────────────────────────────────────────────────────────
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'user'    => $this->userPayload($request->user()),
        ]);
    }

    // ── Google OAuth ──────────────────────────────────────────────────────────
    // Called by AuthContext.jsx → POST /api/auth/google
    // Expects: { access_token: "<Google OAuth2 access token>" }
    //
    // Flow:
    //   1. Exchange access_token for Google user info via userinfo endpoint
    //   2. Find existing user by google_id, then fall back to email
    //   3. Create a new user if none found (is_new_user = true)
    //   4. Issue a Sanctum token and return user payload
    public function googleLogin(Request $request)
    {
        $request->validate([
            'access_token' => 'required|string|max:2048',
        ]);

        try {
            // ── Fetch user info from Google ───────────────────────────────────
            $googleResponse = Http::timeout(10)
                ->withToken($request->access_token)
                ->get('https://www.googleapis.com/oauth2/v3/userinfo');

            if (! $googleResponse->ok()) {
                Log::channel('security')->warning('Auth: Google userinfo call failed', [
                    'ip'     => $request->ip(),
                    'status' => $googleResponse->status(),
                ]);
                return response()->json([
                    'message' => 'Invalid or expired Google token. Please try again.',
                ], 401);
            }

            $gUser    = $googleResponse->json();
            $googleId = $gUser['sub']     ?? null;
            $email    = isset($gUser['email']) ? strtolower(trim($gUser['email'])) : null;
            $name     = $gUser['name']    ?? null;
            $avatar   = $gUser['picture'] ?? null;

            if (! $googleId || ! $email) {
                return response()->json([
                    'message' => 'Could not retrieve account details from Google.',
                ], 422);
            }

            // ── Find or create the user ───────────────────────────────────────
            $user = User::where('google_id', $googleId)->first()
                ?? User::whereRaw('LOWER(email) = ?', [$email])->first();

            $isNewUser = false;

            if ($user) {
                // Link google_id if this email already existed without it
                $updates = [];
                if (! $user->google_id) {
                    $updates['google_id'] = $googleId;
                }
                // Backfill avatar only when user has none
                if ($avatar && ! $user->avatar) {
                    $updates['avatar'] = $avatar;
                }
                if (! empty($updates)) {
                    $user->update($updates);
                }
            } else {
                $isNewUser = true;
                $username  = $this->generateGoogleUsername($name ?? $email);

                $user = User::create([
                    'name'      => $name     ?? $username,
                    'username'  => $username,
                    'email'     => $email,
                    'password'  => Hash::make(Str::random(32)), // unusable random password
                    'avatar'    => $avatar,
                    'google_id' => $googleId,
                    'role'      => 'customer',
                ]);
            }

            // ── Guard: banned users cannot sign in via Google either ───────────
            if ($user->is_banned) {
                Log::channel('security')->notice('Auth: banned user Google login attempt', [
                    'user_id' => $user->id,
                    'ip'      => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Your account has been suspended. Please contact support.',
                ], 403);
            }

            // ── Issue token (one session at a time) ───────────────────────────
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            Log::channel('security')->info('Auth: Google login success', [
                'user_id'    => $user->id,
                'is_new'     => $isNewUser,
                'ip'         => $request->ip(),
            ]);

            return response()->json([
                'success'     => true,
                'token'       => $token,
                'user'        => $this->userPayload($user),
                'is_new_user' => $isNewUser,
            ]);

        } catch (\Throwable $e) {
            Log::channel('security')->error('Auth: Google login exception', [
                'ip'    => $request->ip(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Google sign-in failed. Please try again.',
            ], 500);
        }
    }

    // ── Forgot password ───────────────────────────────────────────────────────
    public function forgotPassword(Request $request)
    {
        // Always return the same message regardless of whether email exists
        // — prevents email enumeration attacks
        $request->validate(['email' => 'required|email:rfc|max:254']);

        Password::sendResetLink($request->only('email'));

        return response()->json([
            'message' => 'If that email is registered, a reset link has been sent.',
        ]);
    }

    // ── Reset password ────────────────────────────────────────────────────────
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email:rfc|max:254',
            'password' => $this->passwordRules(),
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
                // Revoke ALL tokens on password reset — full session invalidation
                $user->tokens()->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password reset successfully. You can now log in.']);
        }

        return response()->json([
            'message' => $status === Password::INVALID_TOKEN
                ? 'Invalid or expired reset link. Please request a new one.'
                : 'Failed to reset password. Please try again.',
        ], 422);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Shared password rules used by register and resetPassword.
     *
     * uncompromised() makes an external call to HaveIBeenPwned.
     * Guard it with a try/catch so a HIBP outage never breaks auth.
     * Disable entirely with PWNED_CHECK=false in .env.
     */
    protected function passwordRules(): array
    {
        $rule = PasswordRule::min(8)
            ->mixedCase()
            ->numbers()
            ->symbols();

        if (env('PWNED_CHECK', true)) {
            try {
                $rule->uncompromised();
            } catch (\Throwable $e) {
                // HIBP is unreachable — log and continue without the check.
                // Better to allow a potentially weak password than to block
                // all registrations because of a third-party outage.
                Log::channel('security')->warning('Auth: HIBP check failed, skipping', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return ['required', 'confirmed', $rule];
    }

    /**
     * Throttle key: lowercased identifier + IP.
     * Prevents brute force across email variants (User@, USER@, etc.).
     */
    protected function throttleKey(Request $request): string
    {
        return Str::lower($request->input('username', '')) . '|' . $request->ip();
    }

    private function userPayload(User $user): array
    {
        return [
            'id'        => $user->id,
            'username'  => $user->username,
            'email'     => $user->email,
            'name'      => $user->name,
            'phone'     => $user->phone,
            'avatar'    => $user->avatar,
            'role'      => $user->role ?? 'customer',
            'is_banned' => $user->is_banned ?? false,
        ];
    }

    /**
     * Derive a unique, slug-safe username from a Google display name or email.
     *
     * "John Doe"        → "john_doe"  (or "john_doe_2" if taken)
     * "alice@gmail.com" → "alice"
     */
    private function generateGoogleUsername(string $base): string
    {
        // Strip email domain if an email was passed
        if (str_contains($base, '@')) {
            $base = explode('@', $base)[0];
        }

        // Keep only safe characters, collapse spaces/separators to underscore
        $base = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $base));
        $base = trim($base, '_');

        // Truncate to 40 chars so the suffix "_{n}" still fits within 50
        $base = substr($base, 0, 40) ?: 'user';

        // Ensure uniqueness
        $candidate = $base;
        $i         = 1;
        while (User::whereRaw('LOWER(username) = ?', [$candidate])->exists()) {
            $candidate = $base . '_' . $i++;
        }

        return $candidate;
    }
}