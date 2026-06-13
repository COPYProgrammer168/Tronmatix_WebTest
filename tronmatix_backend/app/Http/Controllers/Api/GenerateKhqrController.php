<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateKhqrController extends Controller
{
    // QR code expires after this many minutes
    private const QR_EXPIRY_MINUTES = 10;
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

    /**
     * Current timestamp in YYYYMMDDHHmmss format (required by PayWay).
     */
    private function reqTime(): string
    {
        return now()->format('YmdHis');
    }

    /**
     * Unique transaction ID = order ID + current Unix timestamp.
     */
    private function tranId(Order $order): string
    {
        return (string) ($order->id . now()->timestamp);
    }

    /**
     * Encode order items to base64 JSON as required by PayWay.
     * Format: base64( [{"name":"...","quantity":1,"price":0.99}, ...] )
     */
    private function encodeItems(Order $order): string
    {
        $items = $order->items->map(fn($i) => [
            'name' => $i->name,
            'quantity' => (int) $i->qty,
            'price' => (float) number_format((float) $i->price, 2, '.', ''),
        ])->values()->toArray();

        return base64_encode(json_encode($items));
    }

    /**
     * Base64-encode a URL (required by PayWay for callback_url).
     */
    private function encodeUrl(string $url): string
    {
        return base64_encode($url);
    }

    private function callbackUrl(): string
    {
        $url = app()->environment('production')
            ? config('services.payway.callback_url')
            : config(
                'services.payway.callback_local_url',
                config('services.payway.callback_url')
            );

        return $this->encodeUrl($url ?: config('app.url') . '/api/payment/webhook');
    }
    // MAIN ENDPOINT: POST /api/payment/generate-qr
    public function generate(Request $request): JsonResponse
    {
        $request->validate(['order_id' => 'required|integer']);

        // ── Auth check ────────────────────────────────────────────────────────
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        // ── Load order ────────────────────────────────────────────────────────
        $order = Order::with('items')->find($request->order_id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }
        if ($order->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }
        if ($order->payment_status === 'paid') {
            return response()->json(['success' => false, 'message' => 'Order is already paid.'], 422);
        }
        if ($order->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Cannot pay a cancelled order.'], 422);
        }

        // ── Reuse existing QR if still valid ──────────────────────────────────
        $existing = Payment::where('order_id', $order->id)
            ->where('status', Payment::STATUS_PENDING)
            ->latest()
            ->first();

        if ($existing && !$existing->isExpired() && $existing->qr_data) {
            Log::info('Returning existing valid QR', ['order_id' => $order->id]);
            return $this->buildResponse($order, $existing->qr_data, $existing->qr_md5, $existing->qr_expires_at, $existing->meta ?? []);
        }

        // ── Expire stale pending payments for this order ──────────────────────
        Payment::where('order_id', $order->id)
            ->where('status', Payment::STATUS_PENDING)
            ->update([
                'status' => Payment::STATUS_EXPIRED,
                'qr_md5' => null, // clear so unique constraint won't block re-generation
            ]);

        // ── Prepare all payload values ────────────────────────────────────────

        $merchantId = config('services.payway.merchant_id');
        $apiBase = rtrim((string) config('services.payway.api_url', ''), '/');
        $reqTime = $this->reqTime();
        $tranId = $this->tranId($order);
        $amount = round((float) $order->total, 2);
        $amountStr = number_format($amount, 2, '.', ''); // e.g. "0.79" not "0.7900..."
        $items = $this->encodeItems($order);
        $firstName = $user->name ?? $user->username ?? '';
        $lastName = '';
        $email = $user->email ?? '';
        $phone = $user->phone ?? '';
        $purchaseType = 'purchase';
        $paymentOption = 'abapay_khqr';
        $callbackUrl = $this->callbackUrl();
        $returnDeeplink = ''; // not used — empty string in hash
        $currency = 'USD';
        $customFields = ''; // not used — empty string in hash
        $returnParams = ''; // not used — empty string in hash
        $payout = ''; // not used — empty string in hash
        $lifetime = self::QR_EXPIRY_MINUTES; // integer: 10
        $qrImageTemplate = 'template3_color';
        $expiresAt = now()->addMinutes(self::QR_EXPIRY_MINUTES);

        // ── Build hash string (19 fields in exact order from PayWay docs) ─────
        $hashData =
            $reqTime         // 1.  req_time
            . $merchantId    // 2.  merchant_id
            . $tranId        // 3.  tran_id
            . $amountStr     // 4.  amount       (2 decimal places, e.g. "0.79")
            . $items         // 5.  items        (base64 JSON)
            . $firstName     // 6.  first_name
            . $lastName      // 7.  last_name
            . $email         // 8.  email
            . $phone         // 9.  phone
            . $purchaseType  // 10. purchase_type
            . $paymentOption // 11. payment_option
            . $callbackUrl   // 12. callback_url  (base64 encoded)
            . $returnDeeplink// 13. return_deeplink (empty)
            . $currency      // 14. currency
            . $customFields  // 15. custom_fields  (empty)
            . $returnParams  // 16. return_params   (empty)
            . $payout        // 17. payout          (empty)
            . $lifetime      // 18. lifetime        (integer → "10")
            . $qrImageTemplate; // 19. qr_image_template

        Log::debug('PayWay hash input', ['data' => $hashData]);

        // ── Generate hash ─────────────────────────────────────────────────────
        try {
            $hash = $this->makeHash($hashData);
        } catch (\Throwable $e) {
            Log::error('PayWay hash failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Payment setup failed.'], 500);
        }

        // ── Build payload for PayWay API ──────────────────────────────────────
        $payload = [
            'req_time' => $reqTime,
            'merchant_id' => $merchantId,
            'tran_id' => $tranId,
            'amount' => $amountStr,
            'items' => $items,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'purchase_type' => $purchaseType,
            'payment_option' => $paymentOption,
            'callback_url' => $callbackUrl,
            'return_deeplink' => null,
            'currency' => $currency,
            'custom_fields' => null,
            'return_params' => null,
            'payout' => null,
            'lifetime' => $lifetime,
            'qr_image_template' => $qrImageTemplate,
            'hash' => $hash,
        ];

        Log::info('PayWay generate-qr request', [
            'order_id' => $order->id,
            'merchant_id' => $merchantId,
            'tran_id' => $tranId,
            'amount' => $amountStr,
        ]);

        // ── Call PayWay API ───────────────────────────────────────────────────
        try {
            $response = Http::timeout(15)
                ->withoutVerifying()
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$apiBase}/generate-qr", $payload);

            $body = $response->json();

            Log::info('PayWay generate-qr response', [
                'http_status' => $response->status(),
                'code' => $body['status']['code'] ?? 'n/a',
                'message' => $body['status']['message'] ?? 'n/a',
            ]);

            // PayWay returns code "0" on success
            if (!$response->successful() || ($body['status']['code'] ?? '') !== '0') {
                $msg = $body['status']['message'] ?? 'PayWay QR generation failed';
                Log::error('PayWay QR error', ['body' => $body]);
                throw new \RuntimeException($msg);
            }

            // ── Extract response values ───────────────────────────────────────
            $qrString = $body['qrString'] ?? null;
            $qrImage = $body['qrImage'] ?? null;
            $deeplink = $body['abapay_deeplink'] ?? null;
            $appStore = $body['app_store'] ?? null;
            $playStore = $body['play_store'] ?? null;

            if (!$qrString) {
                throw new \RuntimeException('PayWay returned empty qrString');
            }

            $md5Hash = md5($qrString);

            $meta = [
                'currency' => 'USD',
                'tran_id' => $tranId,
                'qr_image' => $qrImage,
                'abapay_deeplink' => $deeplink,
                'app_store' => $appStore,
                'play_store' => $playStore,
                'payway_code' => $body['status']['code'] ?? null,
            ];

            // ── Save payment record ───────────────────────────────────────────
            Payment::updateOrCreate(
                ['tran_id' => $tranId],
                [
                    'order_id' => $order->id,
                    'provider' => 'payway',
                    'payment_method' => 'bakong',
                    'currency' => 'USD',
                    'qr_data' => $qrString,
                    'qr_md5' => $md5Hash,
                    'qr_expires_at' => $expiresAt,
                    'amount' => $amount,
                    'status' => Payment::STATUS_PENDING,
                    'paid' => false,
                    'meta' => $meta,
                ]
            );

            return $this->buildResponse($order, $qrString, $md5Hash, $expiresAt, $meta);

        } catch (\Throwable $e) {
            Log::error('PayWay generate-qr exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate QR: ' . $e->getMessage(),
            ], 500);
        }
    }
    // SUCCESS RESPONSE
    private function buildResponse(
        Order $order,
        ?string $qrCode,
        ?string $qrMd5,
        $expiresAt,
        array $meta = []
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => [
                'qr_code' => $qrCode,
                'qr_image' => $meta['qr_image'] ?? null, // base64 PNG → show as <img>
                'abapay_deeplink' => $meta['abapay_deeplink'] ?? null, // open ABA Mobile app
                'app_store' => $meta['app_store'] ?? null,
                'play_store' => $meta['play_store'] ?? null,
                'qr_md5' => $qrMd5,
                'amount' => round((float) $order->total, 2),
                'currency' => 'USD',
                'merchant_name' => config('services.payway.merchant_name', 'Tronmatix'),
                'qr_expiration' => $expiresAt instanceof \Carbon\Carbon
                    ? $expiresAt->toIso8601String()
                    : $expiresAt,
            ],
        ]);
    }
}
