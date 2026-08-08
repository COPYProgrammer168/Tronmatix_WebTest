<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\TelegramService;
use App\Services\TelegramUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckPaymentController extends Controller
{
    private string $merchantId;
    private string $apiBase;

    public function __construct()
    {
        $this->merchantId = config('services.payway.merchant_id', '');
        $this->apiBase = rtrim(config('services.payway.api_url', ''), '/');
    }

    // ── Hash helper ───────────────────────────────────────────────────────────

    /**
     * Build HMAC-SHA512 hash required by PayWay.
     */
    private function makeHash(string $data): string
    {
        $apiKey = config('services.payway.api_key', '');

        if (!$apiKey) {
            throw new \RuntimeException(
                'PayWay API key not set. Add PAYWAY_API_KEY to your .env file.'
            );
        }

        return base64_encode(hash_hmac('sha512', $data, $apiKey, true));
    }

    private function reqTime(): string
    {
        return now()->format('YmdHis');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/payment/verify   (polled by BakongQRPanel every 4s)
    // ─────────────────────────────────────────────────────────────────────────

    public function verify(Request $request): JsonResponse
    {
        $request->validate(['order_id' => 'required|integer']);

        $order = Order::find($request->order_id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }
        if ($order->user_id !== $request->user()?->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        // Fast-path: already paid (no need to poll PayWay again)
        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => true,
                'status' => 'paid',
                'paid_at' => $order->updated_at?->toIso8601String(),
            ]);
        }

        $payment = Payment::where('order_id', $order->id)
            ->where('status', Payment::STATUS_PENDING)
            ->latest()->first();

        if (!$payment) {
            return response()->json(['success' => false, 'status' => 'pending', 'message' => 'No pending payment.'], 404);
        }

        // Check expiry before hitting PayWay
        if ($payment->isExpired()) {
            $payment->markAsExpired();
            return response()->json(['success' => false, 'status' => 'expired', 'message' => 'QR code has expired.'], 400);
        }

        $tranId = $payment->meta['tran_id'] ?? $payment->tran_id ?? null;
        if (!$tranId) {
            return response()->json(['success' => false, 'status' => 'pending', 'message' => 'Payment reference not ready.'], 404);
        }

        // Poll PayWay check-transaction-2
        $result = $this->checkTransaction($tranId);

        if ($result === null) {
            // PayWay unreachable — keep frontend polling
            return response()->json(['success' => false, 'status' => 'pending', 'message' => 'PayWay unreachable.'], 200);
        }

        if ($result['paid']) {
            // ── Step 1: persist payment as paid ──────────────────────────────
            $apv = $result['apv'] ?? $tranId;
            $payment->markAsPaid($apv, $result['data'] ?? []);

            Log::info('Payment confirmed via PayWay polling ✅', [
                'order_id' => $order->id,
                'tran_id' => $tranId,
                'apv' => $apv,
            ]);

            // ── Step 2: reload fresh order with all relations ─────────────────
            $freshOrder = Order::with('items', 'user')->find($order->id);

            // ── Step 3: send Telegram receipt to ADMIN ────────────────────────
            try {
                app(TelegramService::class)->sendPaymentConfirmed($freshOrder, $apv);
                Log::info('Admin Telegram receipt sent ✅', ['order_id' => $order->id]);
            } catch (\Throwable $e) {
                // Log but never let this break the payment confirmation response
                Log::warning('Telegram admin receipt failed: ' . $e->getMessage());
            }

            // ── Step 4: send Telegram receipt to CUSTOMER ─────────────────────
            try {
                app(TelegramUserService::class)->onPaymentConfirmed($freshOrder, $apv);
                Log::info('Customer Telegram receipt sent ✅', [
                    'order_id' => $order->id,
                    'chat_id' => $freshOrder->user?->telegram_chat_id ?? 'not connected',
                ]);
            } catch (\Throwable $e) {
                Log::warning('Telegram customer receipt failed: ' . $e->getMessage());
            }

            // ── Step 4.1: Check for missing phone number ──────────────────────
            $shipping = $freshOrder->shipping;
            if (is_string($shipping)) {
                $shipping = json_decode($shipping, true) ?? [];
            }
            if (empty($shipping['phone'])) {
                Log::info('Missing phone number alert ⚠️', ['order_id' => $order->id]);
                try {
                    app(TelegramService::class)->sendPhoneMissingAdminAlert($freshOrder);
                    app(TelegramUserService::class)->sendPhoneMissingUserAlert($freshOrder);
                } catch (\Throwable $e) {
                    Log::warning('Failed to send phone missing alert: ' . $e->getMessage());
                }
            }

            // ── Step 5: return paid status to frontend ────────────────────────
            return response()->json([
                'success' => true,
                'status' => 'paid',
                'paid_at' => $payment->paid_at?->toIso8601String(),
            ]);
        }

        // Still pending — frontend keeps polling
        return response()->json(['success' => false, 'status' => 'pending'], 200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/payment/confirm-manual
    // ─────────────────────────────────────────────────────────────────────────

    public function confirmManual(Request $request): JsonResponse
    {
        $request->validate(['order_id' => 'required|integer']);

        $order = Order::find($request->order_id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }
        if ($order->user_id !== $request->user()?->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        if ($order->payment_status !== 'paid') {
            $order->update(['payment_status' => 'manual_pending']);
            $payment = Payment::where('order_id', $order->id)->latest()->first();
            $payment?->markAsManualPending();

            // Notify admin on Telegram so they can verify manually
            try {
                $freshOrder = Order::with('items', 'user')->find($order->id);
                app(TelegramService::class)->sendAlert(
                    "⚠️ *Manual Payment Claim*\n\n"
                    . "📦 Order: `#{$freshOrder->order_id}`\n"
                    . "👤 Customer: " . ($freshOrder->user?->username ?? 'Guest') . "\n"
                    . "💰 Amount: \${$freshOrder->total}\n"
                    . "🕐 " . now()->format('d M Y, H:i') . "\n\n"
                    . "Please verify and confirm manually in the admin panel."
                );
            } catch (\Throwable $e) {
                Log::warning('Manual claim Telegram alert failed: ' . $e->getMessage());
            }
        }

        return response()->json(['success' => true, 'status' => 'manual_pending']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/payment/webhook   (PayWay → backend, no auth middleware)
    // PayWay posts to PAYWAY_CALLBACK_URL (prod) or PAYWAY_CALLBACK_LOCAL_URL (dev)
    // ─────────────────────────────────────────────────────────────────────────

    public function webhook(Request $request): JsonResponse
    {
        $data = $request->all();
        Log::info('PayWay webhook received', $data);

        $tranId = $data['tran_id'] ?? null;
        if (!$tranId) {
            Log::warning('PayWay webhook: missing tran_id', $data);
            return response()->json(['status' => 'ok']);
        }

        $payment = Payment::where('tran_id', $tranId)->first();
        if (!$payment) {
            Log::warning('PayWay webhook: payment not found', ['tran_id' => $tranId]);
            return response()->json(['status' => 'ok']);
        }

        $order = Order::find($payment->order_id);
        if (!$order) {
            return response()->json(['status' => 'ok']);
        }

        // PayWay webhook: status 0 = success
        $statusCode = (int) ($data['status'] ?? -1);

        if ($statusCode === 0 && !$order->isPaid()) {
            $apv = $data['apv'] ?? $tranId;
            $payment->markAsPaid($apv);

            Log::info('Order paid via PayWay webhook ✅', [
                'order_id' => $order->id,
                'tran_id' => $tranId,
                'apv' => $apv,
            ]);

            $freshOrder = Order::with('items', 'user')->find($order->id);

            // Admin Telegram receipt
            try {
                app(TelegramService::class)->sendPaymentConfirmed($freshOrder, $apv);
                Log::info('Admin Telegram webhook receipt sent ✅', ['order_id' => $order->id]);
            } catch (\Throwable $e) {
                Log::warning('Telegram admin webhook receipt failed: ' . $e->getMessage());
            }

            // Customer Telegram receipt
            try {
                app(TelegramUserService::class)->onPaymentConfirmed($freshOrder, $apv);
                Log::info('Customer Telegram webhook receipt sent ✅', [
                    'order_id' => $order->id,
                    'chat_id' => $freshOrder->user?->telegram_chat_id ?? 'not connected',
                ]);
            } catch (\Throwable $e) {
                Log::warning('Telegram customer webhook receipt failed: ' . $e->getMessage());
            }
        }

        return response()->json(['status' => 'ok']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE: Poll PayWay check-transaction-2
    // Hash formula (from ABA PayWay API spec):
    //   merchant_id + merchant_auth + request_time
    // ─────────────────────────────────────────────────────────────────────────

    private function checkTransaction(string $tranId): ?array
    {
        $reqTime = $this->reqTime();
        $merchantId = $this->merchantId;

        // ── Build hash ─────────────────────────────────────────────────────────
        // PayWay check-transaction-2 authentication (verified via sandbox):
        //   - merchant_auth is NOT needed (omit entirely)
        //   - Hash = HMAC-SHA512(merchant_id + tran_id, api_key)
        //   - Request: {merchant_id, tran_id, request_time, hash}
        //   - RSA encryption is NOT used for this endpoint
        $hashData = $merchantId . $tranId;

        try {
            $hash = $this->makeHash($hashData);
        } catch (\Throwable $e) {
            Log::error('PayWay check-transaction hash failed: ' . $e->getMessage());
            return null;
        }

        // ── Log debug info ─────────────────────────────────────────────────────
        Log::debug('PayWay check-transaction-2 request', [
            'tran_id'     => $tranId,
            'merchant_id' => $merchantId,
            'request_time' => $reqTime,
            'hash'        => $hash,
        ]);

        // NOTE: merchant_auth is intentionally omitted — PayWay sandbox accepts
        // this endpoint with hash = HMAC-SHA512(merchant_id) alone.
        // The tran_id is included as a query param per PayWay spec.
        $payload = [
            'merchant_id'  => $merchantId,
            'tran_id'      => $tranId,
            'request_time' => $reqTime,
            'hash'         => $hash,
        ];

        try {
            $response = Http::timeout(10)
                ->withoutVerifying()
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->apiBase}/check-transaction-2", $payload);

            if ($response->failed()) {
                Log::warning('PayWay check-transaction HTTP error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $body = $response->json();

            Log::info('PayWay check-transaction response', [
                'tran_id' => $tranId,
                'status_code' => $body['status']['code'] ?? 'n/a',
                'payment_code' => $body['data']['payment_status_code'] ?? 'n/a',
                'status' => $body['data']['payment_status'] ?? 'n/a',
                'full_body' => $body,
            ]);

            // Paid when: status.code == "00" AND payment_status_code == 0
            $statusCode = (string) ($body['status']['code'] ?? '');
            $paid = in_array($statusCode, ['0', '00'], true)
                && isset($body['data']['payment_status_code'])
                && (int) $body['data']['payment_status_code'] === 0;

            return [
                'paid' => $paid,
                'apv' => $body['data']['apv'] ?? '',
                'data' => $body['data'] ?? [],
            ];

        } catch (\Throwable $e) {
            Log::error('PayWay checkTransaction exception: ' . $e->getMessage());
            return null;
        }
    }
}