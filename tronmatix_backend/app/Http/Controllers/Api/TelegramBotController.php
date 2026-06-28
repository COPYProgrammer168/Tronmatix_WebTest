<?php

// app/Http/Controllers/Api/TelegramBotController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * webhook() was reading config('services.telegram.webhook_secret')
 * which is the ADMIN bot config — that key doesn't exist there.
 * Result: $secret was always '' → security check was silently skipped,
 * meaning ANY request could trigger the webhook (no protection).
 * Fixed to read config('services.telegram_user.webhook_secret').
 */
class TelegramBotController extends Controller
{
    public function __construct(private TelegramBotService $bot) {}

    // ── POST /api/telegram/bot-webhook  (PUBLIC — no Sanctum) ─────────────────
    public function webhook(Request $request): JsonResponse
    {
        $secret = config('services.telegram_user.webhook_secret', '');

        if ($secret && $request->header('X-Telegram-Bot-Api-Secret-Token') !== $secret) {
            Log::warning('[UserBot] webhook: invalid secret token');
            return response()->json(['ok' => false], 403);
        }

        $update = $request->all();
        Log::info('[UserBot] webhook received', [
            'type' => isset($update['callback_query']) ? 'callback_query'
                : (isset($update['message']) ? 'message' : 'other'),
        ]);

        if (empty($update)) {
            return response()->json(['ok' => true]);
        }

        try {
            $this->bot->handleUpdate($update);
        } catch (\Throwable $e) {
            // Never return 5xx — Telegram retries indefinitely on errors
            Log::error('[UserBot] webhook handler error: '.$e->getMessage());
        }

        // Always 200 immediately — Telegram has a 60s timeout
        return response()->json(['ok' => true]);
    }

    // ── POST /api/telegram/setup-webhook  (protected) ─────────────────────────
    public function setupWebhook(Request $request): JsonResponse
    {
        $url = rtrim(config('app.url'), '/') . '/api/telegram/bot-webhook';

        $result = $this->bot->registerWebhook($url);

        Log::info('[UserBot] setupWebhook called', ['url' => $url, 'result' => $result]);

        return response()->json([
            'success' => $result['ok'] ?? false,
            'url' => $url,
            'result' => $result,
        ]);
    }

    // ── POST /api/telegram/delete-webhook  (protected) ────────────────────────
    public function deleteWebhook(): JsonResponse
    {
        $result = $this->bot->deleteWebhook();

        Log::info('[UserBot] deleteWebhook called', ['result' => $result]);

        return response()->json([
            'success' => $result['ok'] ?? false,
            'message' => ($result['ok'] ?? false)
                ? 'Webhook deleted. Bot is now in polling-free state. Call setup-webhook to re-register.'
                : 'Failed to delete webhook.',
            'result'  => $result,
        ]);
    }

    // ── GET /api/telegram/webhook-info  (protected) ───────────────────────────
    public function webhookInfo(): JsonResponse
    {
        return response()->json($this->bot->getWebhookInfo());
    }

    // ── POST /api/telegram/set-commands  (protected) ──────────────────────────
    public function setCommands(): JsonResponse
    {
        $result = $this->bot->setBotCommands();

        return response()->json([
            'success' => $result['ok'] ?? false,
            'result'  => $result,
        ]);
    }
}