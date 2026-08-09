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
                    // No real email — Telegram doesn't provide one. Use a unique
                    // placeholder (users.email is NOT NULL in the DB).
                    'email'                  => $this->generateTelegramEmail($telegramId),
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
     * Verify a Telegram-signed payload hash (Login Widget OR Mini App initData).
     *
     * Algorithm (from Telegram docs):
     *   secret_key = SHA256(bot_token)          ← NOT HMAC, just raw SHA256
     *   data_check_string = sorted key=value pairs (excluding hash), joined by \n
     *   expected = HMAC-SHA256(data_check_string, secret_key)
     *
     * The token differs by flow:
     *   - Login Widget  → services.telegram.bot_token (admin bot)
     *   - Mini App initData → services.telegram_user.bot_token (user-facing bot
     *     that OWNS the mini app). Telegram only injects initData for the bot the
     *     mini app belongs to, so this is the correct signing key.
     */
    private function verifyTelegramHash(array $data, ?string $botToken = null): bool
    {
        $botToken = $botToken
            ?: config('services.telegram.bot_token')
                ?: env('TELEGRAM_BOT_TOKEN', '');

        if (! $botToken) {
            // If bot token is not configured, skip verification in local dev
            // but log a warning so it isn't silently skipped in production.
            Log::channel('security')->warning('TelegramAuth: bot token not set — skipping hash verification');
            return app()->environment('local', 'testing');
        }

        $hash = $data['hash'] ?? null;
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

        return is_string($hash) && hash_equals($expected, $hash);
    }

    /**
     * POST /api/auth/telegram/mini-app
     *
     * Telegram Mini App silent login. The Mini App (opened via the bot's
     * web_app button) sends its raw initData query string. We validate it with
     * the USER-FACING bot token (the bot that owns the Mini App), find-or-create
     * the user by telegram id, and return a Sanctum token so the storefront can
     * auto-login without any widget click or token deep-link.
     */
    public function handleMiniApp(Request $request)
    {
        $initData = trim((string) $request->input('initData', ''));

        if ($initData === '') {
            return response()->json(['message' => 'Missing initData.'], 422);
        }

        // ── 1. Parse initData into key=value pairs (URL query string format) ──
        parse_str($initData, $parsed);

        if (empty($parsed['hash']) || empty($parsed['user'])) {
            return response()->json(['message' => 'Invalid initData payload.'], 422);
        }

        // ── 2. Verify signature with the USER bot token ───────────────────────
        // verifyTelegramHash() extracts + removes the hash itself, so pass the
        // parsed array as-is (with hash still present).
        $userBotToken = config('services.telegram_user.bot_token')
            ?: env('TELEGRAM_USER_BOT_TOKEN', '');

        if (! $this->verifyTelegramHash($parsed, $userBotToken)) {
            Log::channel('security')->warning('TelegramAuth MiniApp: invalid initData signature');
            return response()->json(['message' => 'Telegram verification failed.'], 401);
        }

        // ── 3. auth_date freshness (prevent replay) ────────────────────────────
        $authDate = (int) ($parsed['auth_date'] ?? 0);
        if ($authDate && (time() - $authDate) > 86_400) {
            return response()->json(['message' => 'Telegram session has expired. Please try again.'], 401);
        }

        // ── 4. Extract the Telegram user object (JSON string inside initData) ──
        $tgUser = json_decode((string) $parsed['user'], true);
        if (! is_array($tgUser) || empty($tgUser['id'])) {
            return response()->json(['message' => 'Invalid Telegram user payload.'], 422);
        }

        $telegramId       = (string) $tgUser['id'];
        $telegramUsername = $tgUser['username'] ?? null;
        $firstName        = (string) ($tgUser['first_name'] ?? '');
        $lastName         = (string) ($tgUser['last_name'] ?? '');
        $photoUrl         = $tgUser['photo_url'] ?? null;
        $fullName         = trim($firstName . ' ' . $lastName);

        // ── 5. Find or create the user (same logic as handleCallback) ─────────
        try {
            $user = User::where('telegram_chat_id', $telegramId)->first();

            $isNewUser = false;
            if ($user) {
                $updates = [];
                if ($telegramUsername && $user->telegram_username !== $telegramUsername) {
                    $updates['telegram_username'] = $telegramUsername;
                }
                if ($photoUrl && ! $user->avatar) $updates['avatar'] = $photoUrl;
                if (! $user->telegram_connected_at) $updates['telegram_connected_at'] = now();
                if (! empty($updates)) $user->update($updates);
            } else {
                $isNewUser = true;
                $username  = $telegramUsername
                    ? $this->generateUsername($telegramUsername)
                    : $this->generateUsername($fullName ?: 'user');

                $user = User::create([
                    'name'                  => $fullName ?: $username,
                    'username'              => $username,
                    // No real email — Telegram doesn't provide one. Use a unique
                    // placeholder (users.email is NOT NULL in the DB).
                    'email'                 => $this->generateTelegramEmail($telegramId),
                    'password'              => Hash::make(Str::random(32)),
                    'avatar'                => $photoUrl,
                    'telegram_chat_id'      => $telegramId,
                    'telegram_username'     => $telegramUsername,
                    'telegram_connected_at' => now(),
                    'role'                  => 'customer',
                ]);
            }

            // ── 6. Banned guard ────────────────────────────────────────────────
            if ($user->isBanned()) {
                Log::channel('security')->notice('TelegramAuth MiniApp: banned user attempt', [
                    'user_id'     => $user->id,
                    'telegram_id' => $telegramId,
                    'ip'          => $request->ip(),
                ]);
                return response()->json(['message' => 'Your account has been suspended. Please contact support.'], 403);
            }

            // ── 7. Issue Sanctum token ─────────────────────────────────────────
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            Log::channel('security')->info('TelegramAuth MiniApp: success', [
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
            Log::channel('security')->error('TelegramAuth MiniApp: exception', [
                'ip'    => $request->ip(),
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Telegram sign-in failed. Please try again.'], 500);
        }
    }

    /**
     * Users.email is NOT NULL, but Telegram provides no email. Generate a unique
     * placeholder address so Telegram-created accounts can be saved. The random
     * suffix guards against two Telegram users sharing the same resolved id.
     */
    private function generateTelegramEmail(string $telegramId): string
    {
        $base = 'tg_' . preg_replace('/[^a-zA-Z0-9]/', '', $telegramId) . '@telegram.local';

        $candidate = $base;
        $i         = 1;
        while (User::where('email', $candidate)->exists()) {
            $candidate = 'tg_' . preg_replace('/[^a-zA-Z0-9]/', '', $telegramId) . '_' . ($i++) . '@telegram.local';
        }

        return $candidate;
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
    // Generates a token for unauthenticated login flow
    public function generateLoginToken(Request $request)
    {
        $token = \Illuminate\Support\Str::random(32);

        \App\Models\TelegramConnectionToken::create([
            'token' => $token,
            'user_id' => null,   // no user yet — bot will create/find on confirm
            'expires_at' => now()->addMinutes(5),
        ]);

        return response()->json(['success' => true, 'token' => $token]);
    }

    // Frontend polls this — returns auth token once bot confirms
    public function checkLoginToken(Request $request)
    {
        $tokenStr = $request->query('token');
        $record = \App\Models\TelegramConnectionToken::where('token', $tokenStr)
            ->whereNotNull('user_id')           // bot has claimed it
            ->where('expires_at', '>', now())
            ->with('user')
            ->first();

        if (!$record || !$record->user) {
            return response()->json(['success' => false]);
        }

        // Issue Sanctum token and clean up
        $record->user->tokens()->delete();
        $authToken = $record->user->createToken('auth_token')->plainTextToken;
        $record->delete();

        return response()->json([
            'success' => true,
            'token' => $authToken,
            'user' => $this->userPayload($record->user),
            'is_new_user' => false,
        ]);
    }
}
