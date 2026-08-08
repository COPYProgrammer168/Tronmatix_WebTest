<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    /**
     * GET /dashboard/password/email
     * Show the "forgot password" form (email method).
     */
    public function showForgotForm(Request $request)
    {
        $mode = $request->input('mode', 'staff'); // admin | staff — from the login toggle

        return view('dashboard.auth.forgot-password', compact('mode'));
    }

    /**
     * POST /dashboard/password/email
     * Send a reset-link email to the account (if it exists in the mode's table).
     */
    public function sendResetLink(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'mode'  => ['required', 'in:admin,staff'],
        ]);

        $email = Str::lower(trim($data['email']));
        $mode  = $data['mode'];
        $ip    = $request->ip();

        // Thresholds — see config/security.php.
        $ipMaxAttempts = (int) config('security.forgot_password.ip_max_attempts', 10);
        $ipLockoutSecs = (int) config('security.forgot_password.ip_lockout_minutes', 60) * 60;
        $cooldownSecs  = (int) config('security.forgot_password.email_cooldown_minutes', 60) * 60;

        // Two independent counters.
        $ipKey    = 'forgot:ip:' . $ip;                     // per-IP (any email, any mode)
        $emailKey = 'forgot:email:' . $mode . ':' . $email; // per-email resubmission cooldown

        // 1) Resubmission cooldown — SAME email already submitted successfully.
        if (RateLimiter::tooManyAttempts($emailKey, 1)) {
            $seconds = RateLimiter::availableIn($emailKey);
            $minutes = (int) ceil($seconds / 60);

            return back()
                ->withInput($request->only('email', 'mode'))
                ->with('cooldown_seconds', $seconds)
                ->withErrors(['email' => "You have already submitted this email. Please wait {$minutes} minute(s)."]);
        }

        // 2) Per-IP attempt threshold / temporary ban.
        if (RateLimiter::tooManyAttempts($ipKey, $ipMaxAttempts)) {
            $seconds = RateLimiter::availableIn($ipKey);
            $minutes = (int) ceil($seconds / 60);

            return back()
                ->withInput($request->only('email', 'mode'))
                ->with('ban_seconds', $seconds)
                ->withErrors(['email' => "Too many attempts from this address. Please try again in {$minutes} minute(s)."]);
        }

        // 3) Explicit account-existence check. A not-found attempt increments the
        //    per-IP counter — enumeration/brute-force protection.
        $model = $mode === 'admin' ? Admin::class : Staff::class;
        $user  = $model::where('email', $email)->first();

        if (! $user) {
            RateLimiter::hit($ipKey, $ipLockoutSecs);
            \App\Services\ActivityLogger::passwordResetFailed($email, $mode, 'account_not_found', $request);

            return back()
                ->withInput($request->only('email', 'mode'))
                ->withErrors(['email' => 'No account found with this email.']);
        }

        // 4) Account exists — send the reset link (lowercased email).
        $status = Password::broker($this->brokerFor($mode))->sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            // Safety net (e.g. account deleted between check and send).
            RateLimiter::hit($ipKey, $ipLockoutSecs);
            \App\Services\ActivityLogger::passwordResetFailed($email, $mode, 'send_failed', $request);

            return back()
                ->withInput($request->only('email', 'mode'))
                ->withErrors(['email' => 'No account found with this email.']);
        }

        // Success: reset the IP counter (fresh slate) but keep the email cooldown.
        RateLimiter::clear($ipKey);
        RateLimiter::hit($emailKey, $cooldownSecs);
        \App\Services\ActivityLogger::passwordResetRequested($email, $mode, $request);

        return back()->with('status', 'If that account exists, a password reset link has been sent to its email.');
    }

    /**
     * GET /dashboard/password/reset/{token}
     * Show the "set new password" form for a valid reset token.
     */
    public function showResetForm(Request $request, string $token)
    {
        $mode  = $request->input('mode', 'staff');
        $email = $request->input('email');

        return view('dashboard.auth.reset-password', compact('token', 'email', 'mode'));
    }

    /**
     * POST /dashboard/password/reset
     * Validate the reset token and set the new password.
     */
    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'token'                 => ['required', 'string'],
            'email'                 => ['required', 'email'],
            'mode'                  => ['required', 'in:admin,staff'],
            'password'              => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
            'password_confirmation' => ['required'],
        ]);

        $status = Password::broker($this->brokerFor($data['mode']))->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                // One active session per user — invalidate any existing tokens.
                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('dashboard.login')
                ->with('success', 'Your password has been reset. You can now log in.');
        }

        $message = match ($status) {
            Password::INVALID_TOKEN    => 'This reset link is invalid or has expired.',
            Password::INVALID_USER     => 'No account found with that email.',
            Password::INVALID_PASSWORD => 'The password does not meet the requirements.',
            default                    => 'Failed to reset your password. Please try again.',
        };

        return back()
            ->withInput($request->only('email', 'mode'))
            ->withErrors(['password' => $message]);
    }

    /**
     * Map the form's mode value ('admin'|'staff') to the actual password-broker
     * name in config/auth.php ('admins'|'staff'). The form/URL use 'admin', but
     * the broker is registered as 'admins' — mismatching throws
     * "Password resetter [admin] is not defined".
     */
    private function brokerFor(string $mode): string
    {
        return $mode === 'admin' ? 'admins' : 'staff';
    }
}
