<?php

// app/Http/Controllers/Api/TelegramAuthController.php
//
// Handles POST /api/auth/telegram  (public — no auth required)
// Called by AuthModal.jsx after the Telegram Login Widget fires onTelegramAuth.
//
// The widget sends a signed data object:
//   { id, first_name, last_name?, username?, photo_url?, auth_date, hash }
//
// We verify the HMAC-SHA256 signature using TELEGRAM_BOT_TOKEN,
// then find-or-create the user, issue a Sanctum token, and return
// { success, token, user, is_new_user }.

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramAuthController extends Controller
{
    /**
     * POST /api/auth/telegram
     *
     * Expected body (all fields from Telegram Login Widget):
     *   id, first_name, last_name (optional), username (optional),
     *   photo_url (optional), auth_date, hash
     */
    public function handleCallback(Request $request)
    {
        $data = $request->validate([
            'id'         => 'required|integer',
            'first_name' => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'username'   => 'nullable|string|max:255',
            'photo_url'  => 'nullable|url|max:500',
            'auth_date'  => 'required|integer',
            'hash'       => 'required|string|size:64',
        ]);

        // ── 1. Verify Telegram signature ──────────────────────────────────────
        if (! $this->verifyTelegramHash($data)) {
            Log::channel('security')->warning('TelegramAuth: invalid hash', [
                'ip'          => $request->ip(),
                'telegram_id' => $data['id'],
            ]);
            return response()->json([
                'message' => 'Telegram authentication verification failed.',
            ], 401);
        }

        // auth_date must be within the last 24 hours (86 400 s) to prevent replay attacks
        if ((time() - (int) $data['auth_date']) > 86_400) {
            return response()->json([
                'message' => 'Telegram session has expired. Please try again.',
            ], 401);
        }

        $telegramId       = (string) $data['id'];
        $telegramUsername = $data['username'] ?? null;
        $firstName        = $data['first_name'];
        $lastName         = $data['last_name'] ?? '';
        $photoUrl         = $data['photo_url'] ?? null;
        $fullName         = trim($firstName . ' ' . $lastName);

        try {
            // ── 2. Find or create user ────────────────────────────────────────
            // Look up by telegram_chat_id first; fall back to telegram_username.
            $user = User::where('telegram_chat_id', $telegramId)->first()
                ?? ($telegramUsername
                    ? User::where('telegram_username', $telegramUsername)->first()
                    : null);

            $isNewUser = false;

            if ($user) {
                // Refresh telegram fields in case they changed
                $updates = [];
                if ($user->telegram_chat_id !== $telegramId)         $updates['telegram_chat_id']     = $telegramId;
                if ($telegramUsername && $user->telegram_username !== $telegramUsername)
                                                                      $updates['telegram_username']    = $telegramUsername;
                if ($photoUrl && ! $user->avatar)                    $updates['avatar']               = $photoUrl;
                if (! $user->telegram_connected_at)                  $updates['telegram_connected_at'] = now();
                if (! empty($updates)) $user->update($updates);
            } else {
                // New user via Telegram
                $isNewUser = true;
                $username  = $telegramUsername
                    ? $this->generateUsername($telegramUsername)
                    : $this->generateUsername($fullName ?: 'user');

                $user = User::create([
                    'name'                   => $fullName ?: $username,
                    'username'               => $username,
                    // No email — Telegram doesn't provide one.
                    // Email is nullable or must be set via ProfileSetupModal later.
                    'email'                  => null,
                    'password'               => Hash::make(Str::random(32)),
                    'avatar'                 => $photoUrl,
                    'telegram_chat_id'       => $telegramId,
                    'telegram_username'      => $telegramUsername,
                    'telegram_connected_at'  => now(),
                    'role'                   => 'customer',
                ]);
            }

            // ── 3. Guard: banned users ────────────────────────────────────────
            if ($user->isBanned()) {
                Log::channel('security')->notice('TelegramAuth: banned user attempt', [
                    'user_id'     => $user->id,
                    'telegram_id' => $telegramId,
                    'ip'          => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Your account has been suspended. Please contact support.',
                ], 403);
            }

            // ── 4. Issue token ────────────────────────────────────────────────
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            Log::channel('security')->info('TelegramAuth: success', [
                'user_id'  => $user->id,
                'is_new'   => $isNewUser,
                'ip'       => $request->ip(),
            ]);

            return response()->json([
                'success'     => true,
                'token'       => $token,
                'user'        => $this->userPayload($user),
                'is_new_user' => $isNewUser,
            ]);

        } catch (\Throwable $e) {
            Log::channel('security')->error('TelegramAuth: exception', [
                'ip'    => $request->ip(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Telegram sign-in failed. Please try again.',
            ], 500);
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Verify the Telegram Login Widget hash.
     *
     * Algorithm (from Telegram docs):
     *   secret_key = SHA256(bot_token)          ← NOT HMAC, just raw SHA256
     *   data_check_string = sorted key=value pairs (excluding hash), joined by \n
     *   expected = HMAC-SHA256(data_check_string, secret_key)
     */
    private function verifyTelegramHash(array $data): bool
    {
        $botToken = config('services.telegram.bot_token')
            ?: env('TELEGRAM_BOT_TOKEN', '');

        if (! $botToken) {
            // If bot token is not configured, skip verification in local dev
            // but log a warning so it isn't silently skipped in production.
            Log::channel('security')->warning('TelegramAuth: TELEGRAM_BOT_TOKEN not set — skipping hash verification');
            return app()->environment('local', 'testing');
        }

        $hash = $data['hash'];
        unset($data['hash']);

        // Build the data-check string: alphabetically sorted key=value lines
        $parts = [];
        foreach ($data as $key => $value) {
            if ($value !== null && $value !== '') {
                $parts[] = $key . '=' . $value;
            }
        }
        sort($parts);
        $dataCheckString = implode("\n", $parts);

        $secretKey = hash('sha256', $botToken, true); // raw binary
        $expected  = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($expected, $hash);
    }

    /**
     * Derive a unique slug-safe username from a Telegram username or display name.
     */
    private function generateUsername(string $base): string
    {
        if (str_contains($base, '@')) {
            $base = explode('@', $base)[0];
        }

        $base = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $base));
        $base = trim($base, '_');
        $base = substr($base, 0, 40) ?: 'user';

        $candidate = $base;
        $i         = 1;
        while (User::whereRaw('LOWER(username) = ?', [$candidate])->exists()) {
            $candidate = $base . '_' . $i++;
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