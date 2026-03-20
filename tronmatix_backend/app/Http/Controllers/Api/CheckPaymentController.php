<?php

// app/Http/Controllers/Api/CheckPaymentController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckPaymentController extends Controller
{
    private string $apiUrl;

    private string $token;

    public function __construct()
    {
        $this->apiUrl = rtrim(config('services.bakong.api_url', 'https://api-bakong.nbc.gov.kh/v1'), '/');
        $this->token = config('services.bakong.token', '');
    }

    // =========================================================================
    // GET /api/payment/verify   (polled by BakongQRPanel every 4 s)
    // =========================================================================
    public function verify(Request $request): JsonResponse
    {
        $request->validate(['order_id' => 'required|integer']);

        $order = Order::find($request->order_id);
        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        // Ownership guard
        if ($order->user_id !== $request->user()?->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        // Fast-path: already confirmed
        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => true,
                'status' => 'paid',
                'bakong_hash' => $order->payment_ref,
                'paid_at' => $order->updated_at?->toIso8601String(),
            ]);
        }

        $payment = Payment::where('order_id', $order->id)
            ->where('status', Payment::STATUS_PENDING)
            ->latest()->first();

        if (! $payment) {
            // FIX [3]: success:false + 404 → frontend keeps polling correctly
            return response()->json(['success' => false, 'status' => 'pending', 'message' => 'No pending payment found.'], 404);
        }

        // FIX [2]: use model's isExpired() — checks qr_expires_at, expires_at AND legacy BIGINT ms
        if ($payment->isExpired()) {
            $payment->markAsExpired();

            return response()->json(['success' => false, 'status' => 'expired', 'message' => 'QR code has expired.'], 400);
        }

        if (! $payment->qr_md5) {
            // FIX: qr_md5 is null when Bakong API failed and static PayWay URL was used as fallback.
            // Return 404 (pending) not 400 — so frontend keeps polling instead of treating it as expired.
            return response()->json(['success' => false, 'status' => 'pending', 'message' => 'No QR MD5 — waiting for Bakong registration.'], 404);
        }

        // FIX [8]: handle null (network error) — treat as still-pending, never crash
        $result = $this->checkByMd5($payment->qr_md5);

        if ($result === null) {
            // Network blip — keep polling
            return response()->json(['success' => false, 'status' => 'pending', 'message' => 'Bakong API unreachable.'], 404);
        }

        if ($result['paid']) {
            // FIX [4,5,10]: use Payment::markAsPaid() which sets status='paid', paid=true,
            //               paid_at=now() (Carbon), bakong_hash, and syncs order payment_status
            $payment->markAsPaid($result['hash'], $result['data']);

            Log::info('Payment confirmed via polling ✅', ['order_id' => $order->id]);

            // FIX [9]: Telegram notification
            try {
                app(TelegramService::class)->sendPaymentConfirmed($order->fresh(), $result['hash']);
            } catch (\Throwable $e) {
                Log::warning('Telegram confirm failed: '.$e->getMessage());
            }

            return response()->json([
                'success' => true,
                'status' => 'paid',
                'bakong_hash' => $result['hash'],
                'paid_at' => $payment->paid_at?->toIso8601String(),
            ]);
        }

        // FIX [3]: not found → success:false + 404 keeps frontend poll alive
        return response()->json(['success' => false, 'status' => 'pending'], 404);
    }

    // =========================================================================
    // POST /api/payment/confirm-manual   ("I paid" fallback button)
    // =========================================================================
    public function confirmManual(Request $request): JsonResponse
    {
        $request->validate(['order_id' => 'required|integer']);

        $order = Order::find($request->order_id);
        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }
        if ($order->user_id !== $request->user()?->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        // FIX [7]: only transition pending → manual_pending, never force-set 'paid'
        if (! in_array($order->payment_status, ['paid'])) {
            $order->update(['payment_status' => 'manual_pending']);

            $payment = Payment::where('order_id', $order->id)->latest()->first();
            $payment?->markAsManualPending();
        }

        return response()->json(['success' => true, 'status' => 'manual_pending']);
    }

    // =========================================================================
    // POST /api/payment/webhook   (ABA PayWay → server — no auth middleware)
    // =========================================================================
    public function webhook(Request $request): JsonResponse
    {
        $data = $request->all();
        Log::info('ABA PayWay webhook received', $data);

        $tranId = $data['tran_id'] ?? $data['bill_number'] ?? null;
        if (! $tranId) {
            Log::warning('ABA webhook: missing tran_id', $data);

            return response()->json(['status' => 'ok']); // 200 stops retries
        }

        // Strategy 1: match by qr_md5
        $payment = Payment::where('qr_md5', $tranId)->first();

        // Strategy 2: extract numeric order ID from 'TRX-00000123'
        if (! $payment) {
            $numericId = (int) preg_replace('/\D/', '', $tranId);
            $payment = $numericId
                ? Payment::where('order_id', $numericId)->latest()->first()
                : null;
        }

        if (! $payment) {
            Log::warning('ABA webhook: payment not found', ['tran_id' => $tranId]);

            return response()->json(['status' => 'ok']);
        }

        $order = Order::find($payment->order_id);
        if (! $order) {
            Log::warning('ABA webhook: order missing for payment', ['payment_id' => $payment->id]);

            return response()->json(['status' => 'ok']);
        }

        // ABA PayWay: status 0 = success
        if ((int) ($data['status'] ?? -1) === 0 && ! $order->isPaid()) {
            $apv = $data['apv'] ?? $data['externalRef'] ?? $tranId;
            $payment->markAsPaid($apv); // syncs order.payment_status via model

            Log::info('Order paid via ABA PayWay webhook', ['order_id' => $order->id]);

            try {
                app(TelegramService::class)->sendPaymentConfirmed($order->fresh(), $apv);
            } catch (\Throwable $e) {
                Log::warning('Telegram webhook alert failed: '.$e->getMessage());
            }
        }

        return response()->json(['status' => 'ok']);
    }

    // =========================================================================
    // PRIVATE
    // =========================================================================

    /**
     * Poll Bakong NBC API for transaction by QR MD5.
     * Returns ['paid'=>bool, 'hash'=>string, 'data'=>array] on success,
     * or null on network / HTTP error.
     *
     * FIX [1,8]: method was named checkBakongTransaction() in old verify() call — now consistently checkByMd5().
     */
    private function checkByMd5(string $md5): ?array
    {
        try {
            $response = Http::withToken($this->token)
                ->timeout(10)
                ->post("{$this->apiUrl}/check_transaction_by_md5", ['md5' => $md5]);

            if ($response->failed()) {
                Log::warning('Bakong check_transaction_by_md5 HTTP error', [
                    'http' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null; // FIX [8]: null = network error → caller keeps polling
            }

            $body = $response->json();

            $paid = isset($body['responseCode'])
                && (int) $body['responseCode'] === 0
                && ! empty($body['data']['hash']);

            return [
                'paid' => $paid,
                'hash' => $body['data']['hash'] ?? '',
                'data' => $body['data'] ?? [],
            ];

        } catch (\Throwable $e) {
            Log::error('Bakong checkByMd5 exception', ['error' => $e->getMessage()]);

            return null;
        }
    }
}
