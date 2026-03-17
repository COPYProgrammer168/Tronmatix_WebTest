<?php

// app/Http/Controllers/Api/PaymentController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Piseth\BakongKhqr\BakongKHQR;
use Piseth\BakongKhqr\Models\MerchantInfo;

class PaymentController extends Controller
{
    private const QR_EXPIRY_MINUTES = 10;

    // =========================================================================
    // 1. GENERATE QR
    //    POST /api/payment/generate-qr
    // =========================================================================
    public function generateQr(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'amount' => 'nullable|numeric|min:0.01',
        ]);

        $authUser = $request->user();
        if (! $authUser) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $order = Order::where('id', $request->order_id)
            ->orWhere('order_id', $request->order_id)
            ->first();

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }
        if ($order->user_id !== $authUser->id) {
            return response()->json(['success' => false, 'message' => 'This order does not belong to your account.'], 403);
        }

        // ── Idempotent: return existing un-expired pending payment ────────────
        // FIX [2,3]: was reading meta['qr_md5'] + meta['qr_expiration'] BIGINT.
        //            Now uses real columns + Payment::isExpired() which checks both.
        $existing = Payment::where('order_id', $order->id)
            ->where('status', Payment::STATUS_PENDING)
            ->latest()->first();

        if ($existing && ! $existing->isExpired()) {
            return response()->json([
                'success' => true,
                'message' => 'khqr generated successfully!',
                'data' => [
                    'merchant_name' => config('services.bakong.merchant_name', 'Tronmatix'), // FIX [1]: was 'marchant_name'
                    'id' => $order->id,
                    'qr_code' => $existing->qr_data,
                    'qr_md5' => $existing->qr_md5,     // FIX [2]: real column not meta
                    'amount' => $existing->amount,
                    'currency' => $existing->currency ?? 'USD',
                    'qr_expiration' => $existing->qr_expires_at?->toIso8601String(),
                ],
            ]);
        }

        $merchantName = config('services.bakong.merchant_name', 'Tronmatix');
        $bakongId = config('services.bakong.bakong_id');

        // ── Static fallback if bakong_id not configured ───────────────────────
        if (! $bakongId) {
            Log::warning('KHQR_BAKONG_ID not set — using static fallback');

            return $this->staticFallbackResponse($order, $merchantName);
        }

        // Pre-declare so catch block can safely reference them
        $tranId = 'TRX-'.strtoupper(substr(md5($order->id.uniqid()), 0, 8));
        $amount = round((float) $order->total, 2);
        $expiresAt = now()->addMinutes(self::QR_EXPIRY_MINUTES);
        $qrCode = null;
        $qrMd5 = null;

        try {
            $merchantInfo = new MerchantInfo(
                (string) $bakongId,
                (string) $merchantName,
                (string) config('services.bakong.merchant_city', 'Phnom Penh'),
                'ABA Bank',
                'USD'
            );
            $merchantInfo->amount = $amount;
            $merchantInfo->billNumber = $tranId;
            $merchantInfo->storeLabel = 'Tronmatix';
            $merchantInfo->terminalLabel = 'Online';
            $merchantInfo->mobileNumber = ''; // '' not null — avoids length validation error

            $khqr = new BakongKHQR('');
            $result = $khqr->generateMerchant($merchantInfo);

            if (! is_array($result) || empty($result['qr'])) {
                throw new \RuntimeException('KHQR generation returned empty result');
            }

            $qrCode = $result['qr'];
            $qrMd5 = $result['md5'] ?? null;

            Log::info('KHQR generated', ['order_id' => $order->id, 'md5' => $qrMd5]);

        } catch (\Throwable $e) {
            Log::warning('KHQR generation failed — using static fallback: '.$e->getMessage());

            return $this->staticFallbackResponse($order, $merchantName, $tranId, $expiresAt);
        }

        // FIX [8]: provider was 'aba' — KHQR is bakong
        // FIX [9]: no newline in 'qr_data' key
        Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'tran_id' => $tranId,
                'provider' => 'bakong',    // FIX [8]
                'payment_method' => 'bakong',
                'currency' => 'USD',
                'qr_data' => $qrCode,     // FIX [9]: was 'qr_data\n'
                'qr_md5' => $qrMd5,
                'qr_expires_at' => $expiresAt,
                'amount' => $amount,
                'status' => Payment::STATUS_PENDING,
                'paid' => false,
                'meta' => ['currency' => 'USD'],
            ]
        );

        $order->update(['payment_ref' => $tranId]);

        return response()->json([
            'success' => true,
            'message' => 'khqr generated successfully!',
            'data' => [
                'merchant_name' => $merchantName, // FIX [1]
                'id' => $order->id,
                'qr_code' => $qrCode,
                'qr_md5' => $qrMd5,
                'amount' => $amount,
                'currency' => 'USD',
                'qr_expiration' => $expiresAt->toIso8601String(),
            ],
        ], 201);
    }

    // =========================================================================
    // 2. VERIFY PAYMENT (polling)
    //    POST /api/payment/verify
    // =========================================================================
    public function verify(Request $request)
    {
        $request->validate(['order_id' => 'required']);

        $order = Order::where('id', $request->order_id)
            ->orWhere('order_id', $request->order_id)
            ->first();

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404); // FIX [4]
        }

        if ($order->payment_status === 'paid') {
            return response()->json(['success' => true, 'status' => 'paid']);
        }

        $payment = Payment::where('order_id', $order->id)
            ->where('status', Payment::STATUS_PENDING)
            ->latest()->first();

        if (! $payment) {
            return response()->json(['success' => false, 'status' => 'pending', 'message' => 'No pending payment found'], 404);
        }

        // FIX [6]: use isExpired() which checks qr_expires_at + expires_at + legacy ms
        if ($payment->isExpired()) {
            $payment->markAsExpired(); // FIX [5]: was markExpired(), model method is markAsExpired()

            return response()->json(['success' => false, 'status' => 'expired'], 400);
        }

        $qrMd5 = $payment->getQrMd5Value(); // reads qr_md5 column with meta fallback

        $bakongApiUrl = rtrim(config('services.bakong.api_url'), '/');
        $accessToken = config('services.bakong.token');

        if (! $bakongApiUrl || ! $accessToken) {
            Log::warning('Missing KHQR_BAKONG_API_URL or KHQR_BAKONG_TOKEN');

            return response()->json(['success' => false, 'status' => 'pending', 'message' => 'Payment config missing'], 400);
        }

        try {
            $response = Http::withToken($accessToken)
                ->timeout(10)
                ->post("{$bakongApiUrl}/check_transaction_by_md5", ['md5' => $qrMd5]);

            $data = $response->json();

            if (($data['responseCode'] ?? -1) === 0 && ! empty($data['data']['hash'])) {
                $payment->markAsPaid($data['data']['hash'], $data['data']); // syncs order too

                Log::info('Payment confirmed via polling ✅', ['order_id' => $order->id]);

                try {
                    app(TelegramService::class)->sendPaymentConfirmed($order, $data['data']['hash']);
                } catch (\Throwable $e) {
                    Log::warning('Telegram confirm failed: '.$e->getMessage());
                }

                return response()->json(['success' => true, 'status' => 'paid']);
            }

            Log::info('Payment not found yet ❌', ['order_id' => $order->id]);

            return response()->json(['success' => false, 'status' => 'pending', 'message' => 'Payment not confirmed yet'], 404);

        } catch (\Throwable $e) {
            Log::error('Payment verify error: '.$e->getMessage());

            return response()->json(['success' => false, 'status' => 'pending', 'message' => $e->getMessage()], 404);
        }
    }

    // =========================================================================
    // 3. WEBHOOK
    //    POST /api/payment/webhook
    // =========================================================================
    public function webhook(Request $request)
    {
        Log::info('Payment webhook received', $request->all());

        $tranId = $request->input('tran_id');
        $apv = $request->input('apv') ?? $request->input('externalRef');

        if (! $tranId) {
            return response()->json(['success' => false, 'message' => 'missing tran_id'], 400);
        }

        // FIX [7]: try tran_id first, then qr_md5 fallback (matches CheckPaymentController strategy)
        $payment = Payment::where('tran_id', $tranId)->first()
            ?? Payment::where('qr_md5', $tranId)->first();

        if (! $payment) {
            Log::warning('Webhook: payment not found', ['tran_id' => $tranId]);

            return response()->json(['success' => false, 'message' => 'payment not found'], 404);
        }

        if ($payment->isPaid()) {
            return response()->json(['success' => true, 'message' => 'already paid']);
        }

        $payment->markAsPaid($apv ?: $tranId); // syncs order too via model

        try {
            app(TelegramService::class)->sendPaymentConfirmed($payment->order, $apv ?: $tranId);
        } catch (\Throwable $e) {
            Log::warning('Telegram webhook alert failed: '.$e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'ok']);
    }

    // =========================================================================
    // 4. MANUAL CONFIRM
    //    POST /api/payment/confirm-manual
    // =========================================================================
    public function confirmManual(Request $request)
    {
        $request->validate(['order_id' => 'required']);

        $order = Order::where('id', $request->order_id)
            ->orWhere('order_id', $request->order_id)
            ->firstOrFail();

        if (in_array($order->payment_status, ['pending', null])) {
            $order->update(['payment_status' => 'manual_pending']);

            $payment = Payment::where('order_id', $order->id)->latest()->first();
            $payment?->markAsManualPending();
        }

        try {
            app(TelegramService::class)->sendAlert(
                "⚠️ *Manual Payment Claimed*\n\n".
                "📦 Order: `#{$order->order_id}`\n".
                "💰 Amount: \${$order->total}\n".
                "👤 Customer pressed \"I paid\" — please verify in Bakong portal.\n".
                '🕐 '.now()->format('d M Y, H:i')
            );
        } catch (\Throwable $e) {
            Log::warning('Telegram manual confirm failed: '.$e->getMessage());
        }

        return response()->json(['success' => true, 'status' => 'manual_pending']);
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /** Store static fallback payment and return consistent QR response */
    private function staticFallbackResponse(
        Order $order,
        string $merchantName,
        ?string $tranId = null,
        $expiresAt = null
    ) {
        $staticUrl = config('services.bakong.static_payway_url',
            env('KHQR_STATIC_PAYWAY_URL', 'https://link.payway.com.kh/ABAPAYTD422549V'));

        $tranId = $tranId ?? 'TRX-'.strtoupper(substr(md5($order->id.uniqid()), 0, 8));
        $expiresAt = $expiresAt ?? now()->addMinutes(self::QR_EXPIRY_MINUTES);

        Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'tran_id' => $tranId,
                'provider' => 'bakong',
                'payment_method' => 'bakong',
                'currency' => 'USD',
                'qr_data' => $staticUrl,
                'qr_md5' => null,
                'qr_expires_at' => $expiresAt,
                'amount' => round((float) $order->total, 2),
                'status' => Payment::STATUS_PENDING,
                'paid' => false,
                'meta' => ['currency' => 'USD', 'fallback' => true],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'khqr generated successfully!',
            'data' => [
                'merchant_name' => $merchantName,
                'id' => $order->id,
                'qr_code' => $staticUrl,
                'qr_md5' => null,
                'amount' => round((float) $order->total, 2),
                'currency' => 'USD',
                'qr_expiration' => $expiresAt->toIso8601String(),
            ],
        ]);
    }
}
