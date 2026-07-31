<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
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

        $status = Password::broker($data['mode'])->sendResetLink(
            $request->only('email')
        );

        // Anti-enumeration: always the same message regardless of whether the
        // account exists. The broker uses the admins/staff provider for lookup.
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

        $status = Password::broker($data['mode'])->reset(
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
}
