<?php

// app/Http/Controllers/Dashboard/TelegramAdminController.php
//
// Provides admin UI endpoints for managing the Telegram bot webhook.
// Primary use-case: resolve 409 "Conflict: terminated by other getUpdates request"
//   Step 1 → POST /dashboard/telegram/delete-webhook  (clears polling + stale webhook)
//   Step 2 → POST /dashboard/telegram/setup-webhook   (registers the HTTPS webhook URL)
//   Step 3 → GET  /dashboard/telegram/webhook-info    (verify registration)

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\TelegramBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramAdminController extends Controller
{
    public function __construct(private TelegramBotService $bot) {}

    // ── POST /dashboard/telegram/setup-webhook ────────────────────────────────
    // Body: { "url": "https://yourdomain.com/api/telegram/bot-webhook" }
    public function setupWebhook(Request $request): JsonResponse
    {
        $request->validate(['url' => 'required|url']);

        // Always delete first to avoid 409 conflicts before re-registering
        $this->bot->deleteWebhook();

        $result = $this->bot->registerWebhook($request->input('url'));

        Log::info('[TelegramAdmin] setupWebhook', [
            'url'    => $request->input('url'),
            'result' => $result,
        ]);

        return response()->json([
            'success' => $result['ok'] ?? false,
            'message' => $result['ok'] ?? false
                ? '✅ Webhook registered. Bot is now in webhook mode.'
                : '❌ Failed to register webhook: '.($result['description'] ?? 'unknown error'),
            'result'  => $result,
        ]);
    }

    // ── POST /dashboard/telegram/delete-webhook ───────────────────────────────
    // FIX for 409: This clears the webhook AND drops pending updates.
    // After calling this, NO getUpdates polling will conflict with the webhook.
    // Must be followed by setup-webhook to resume receiving updates.
    public function deleteWebhook(): JsonResponse
    {
        $result = $this->bot->deleteWebhook();

        Log::info('[TelegramAdmin] deleteWebhook', ['result' => $result]);

        return response()->json([
            'success' => $result['ok'] ?? false,
            'message' => $result['ok'] ?? false
                ? '🗑 Webhook deleted. Pending updates dropped. Now call setup-webhook.'
                : '❌ Failed to delete webhook: '.($result['description'] ?? 'unknown error'),
            'result'  => $result,
        ]);
    }

    // ── GET /dashboard/telegram/webhook-info ──────────────────────────────────
    // Returns current webhook registration status from Telegram API.
    // Use this to verify the webhook is correctly set.
    public function webhookInfo(): JsonResponse
    {
        $info = $this->bot->getWebhookInfo();

        return response()->json([
            'success'        => true,
            'webhook_url'    => $info['result']['url'] ?? null,
            'is_set'         => !empty($info['result']['url']),
            'pending_count'  => $info['result']['pending_update_count'] ?? 0,
            'last_error'     => $info['result']['last_error_message'] ?? null,
            'raw'            => $info,
        ]);
    }
}