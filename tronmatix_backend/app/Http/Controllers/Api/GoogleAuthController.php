<?php

// app/Http/Controllers/Api/GoogleAuthController.php
//
// Handles Google OAuth via access_token (from Google Identity Services popup).
// Flow:
//   1. Frontend gets access_token via GSI popup
//   2. POST /api/auth/google { access_token }
//   3. We verify token by calling Google's userinfo endpoint
//   4. Find or create User
//   5. Return { token, user, is_new_user }
//
// is_new_user = true  → frontend shows username/phone setup modal
// is_new_user = false → frontend closes modal, user is logged in

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    public function handleCallback(Request $request): JsonResponse
    {
        $request->validate([
            'access_token' => 'required|string',
        ]);

        // ── Verify token with Google userinfo endpoint ─────────────────────────
        try {
            $response = Http::timeout(10)
                ->withToken($request->access_token)
                ->get('https://www.googleapis.com/oauth2/v3/userinfo');

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'Invalid Google token. Please try again.',
                ], 401);
            }

            $googleUser = $response->json();
        } catch (\Throwable $e) {
            Log::warning('[GoogleAuth] userinfo request failed', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Could not verify Google account. Please try again.',
            ], 503);
        }

        $googleId = $googleUser['sub']    ?? null;
        $email    = $googleUser['email']  ?? null;
        $name     = $googleUser['name']   ?? null;
        $avatar   = $googleUser['picture'] ?? null;

        if (!$googleId || !$email) {
            return response()->json([
                'message' => 'Google did not return required user info.',
            ], 422);
        }

        // ── Find or create user ────────────────────────────────────────────────
        $isNewUser = false;

        // 1. Try to find by google_id (returning user)
        $user = User::where('google_id', $googleId)->first();

        // 2. Try to find by email (existing account — link Google to it)
        if (!$user) {
            $user = User::where('email', strtolower($email))->first();
            if ($user) {
                // Link Google to existing account
                $user->update(['google_id' => $googleId]);
                if (!$user->avatar && $avatar) {
                    $user->update(['avatar' => $avatar]);
                }
            }
        }

        // 3. Create new user
        if (!$user) {
            $isNewUser = true;

            // Generate a unique username from name or email prefix
            $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $name ?? Str::before($email, '@')));
            $baseUsername = Str::limit($baseUsername, 20, '') ?: 'user';
            $username     = $this->uniqueUsername($baseUsername);

            $user = User::create([
                'name'      => $name ?? $username,
                'username'  => $username,
                'email'     => strtolower($email),
                'password'  => Hash::make(Str::random(32)), // random — Google users don't use passwords
                'google_id' => $googleId,
                'avatar'    => $avatar,
                'role'      => 'customer',
            ]);
        }

        // Revoke old tokens, issue fresh one
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success'      => true,
            'token'        => $token,
            'user'         => $this->userPayload($user),
            // Frontend uses this flag to show the "complete your profile" modal
            'is_new_user'  => $isNewUser,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Generate a unique username by appending numbers if taken.
     */
    private function uniqueUsername(string $base): string
    {
        $candidate = $base;
        $i = 1;

        while (User::where('username', $candidate)->exists()) {
            $candidate = $base . $i;
            $i++;
        }

        return $candidate;
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
}
