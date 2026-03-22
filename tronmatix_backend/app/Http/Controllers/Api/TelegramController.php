<?php

// app/Http/Controllers/Api/TelegramController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramUserService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    public function __construct(private TelegramUserService $telegram) {}

    // ── POST /api/telegram/connect ────────────────────────────────────────────
    public function connect(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id'         => 'required|integer',
            'auth_date'  => 'required|integer',
            'hash'       => 'required|string',
            'first_name' => 'nullable|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'username'   => 'nullable|string|max:100',
            'photo_url'  => 'nullable|url',
        ]);

        if (! $this->telegram->verifyLoginHash($validated)) {
            Log::warning('[UserBot] connect: invalid hash', [
                'user_id'     => $request->user()->id,
                'telegram_id' => $validated['id'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Telegram authentication failed. Please try again.',
            ], 422);
        }

        try {
            // connectUser() saves to DB + sends welcome message automatically
            $this->telegram->connectUser($request->user(), $validated);

            Log::info('[UserBot] Telegram connected', [
                'user_id'     => $request->user()->id,
                'telegram_id' => $validated['id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Telegram connected successfully!',
                'data'    => [
                    'telegram_id'       => (string) $validated['id'],
                    'telegram_username' => $validated['username'] ?? null,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('[UserBot] connect failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to connect Telegram.'], 500);
        }
    }

    // ── POST /api/telegram/disconnect ─────────────────────────────────────────
    public function disconnect(Request $request): JsonResponse
    {
        $user = $request->user();

        Log::info('[UserBot] Telegram disconnect requested', [
            'user_id'     => $user->id,
            'telegram_id' => $user->telegram_chat_id,
        ]);

        try {
            // FIX: disconnectUser() now sends alert to Telegram FIRST,
            // then clears the chat_id from DB.
            // Order matters: if we cleared DB first, we'd lose the chat_id
            // and couldn't send the farewell message.
            $this->telegram->disconnectUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Telegram disconnected. You have been notified.',
            ]);
        } catch (\Throwable $e) {
            Log::error('[UserBot] disconnect failed', ['error' => $e->getMessage()]);

            // Still return success — DB might be cleared even if message failed
            return response()->json([
                'success' => true,
                'message' => 'Telegram disconnected.',
            ]);
        }
    }

    // ── POST /api/telegram/test-message ──────────────────────────────────────
    public function testMessage(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->telegram_chat_id) {
            return response()->json([
                'success' => false,
                'message' => 'No Telegram account connected.',
            ], 422);
        }

        $sent = $this->telegram->sendTestMessage($user);

        return response()->json([
            'success' => $sent,
            'message' => $sent
                ? 'Test message sent! Check your Telegram.'
                : 'Failed to send message. Check your bot token configuration.',
        ], $sent ? 200 : 500);
    }

    // ── GET /api/telegram/status ──────────────────────────────────────────────
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        $connectedAt = null;
        if ($user->telegram_connected_at) {
            try {
                $connectedAt = Carbon::parse($user->telegram_connected_at)->toIso8601String();
            } catch (\Throwable) {
                $connectedAt = (string) $user->telegram_connected_at;
            }
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'connected'         => (bool) $user->telegram_chat_id,
                'telegram_username' => $user->telegram_username,
                'connected_at'      => $connectedAt,
            ],
        ]);
    }
}
