<?php

// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ── Register ──────────────────────────────────────────────────────────────
    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|min:3|max:50|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // FIX [3]: 'name' column is NOT NULL — set it to username as fallback
        $user = User::create([
            'name' => $validated['username'],  // FIX [3]
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        // FIX [1,2]: include role, is_banned, avatar, phone in response
        return response()->json([
            'success' => true,
            'user' => $this->userPayload($user),
            'token' => $token,
        ], 201);
    }

    // ── Login ─────────────────────────────────────────────────────────────────
    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $validated['username'])
            ->orWhere('email', $validated['username'])
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Invalid username or password.'],
            ]);
        }

        if ($user->isBanned()) {
            throw ValidationException::withMessages([
                'username' => ['Your account has been suspended.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // FIX [1,2]: include role, is_banned, avatar, phone in response
        return response()->json([
            'success' => true,
            'user' => $this->userPayload($user),
            'token' => $token,
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
        // FIX [1,2]: return full payload including role, avatar, phone
        return response()->json([
            'success' => true,
            'user' => $this->userPayload($request->user()),
        ]);
    }

    // ── Forgot password ───────────────────────────────────────────────────────
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Password reset link sent! Check your email.', 'status' => $status]);
        }

        return response()->json([
            'message' => match ($status) {
                Password::INVALID_USER => 'No account found with that email.',
                Password::RESET_THROTTLED => 'Too many attempts. Please wait.',
                default => 'Failed to send reset email.',
            },
            'status' => $status,
        ], 422);
    }

    // ── Reset password ────────────────────────────────────────────────────────
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
                $user->tokens()->delete(); // invalidate all existing sessions
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password reset successfully! You can now log in.']);
        }

        return response()->json([
            'message' => $status === Password::INVALID_TOKEN
                ? 'Invalid or expired reset token. Please request a new link.'
                : 'Failed to reset password.',
        ], 422);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * FIX [1,2]: Consistent user payload for all auth endpoints.
     * Includes all fields the frontend AuthContext and ProfileHeader need.
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'name' => $user->name,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
            'role' => $user->role ?? 'customer',
            'is_banned' => $user->is_banned ?? false,
        ];
    }
}
