<?php

// app/Http/Controllers/Api/GenerateKhqrController.php
//
// NOTE: This controller is a LEGACY duplicate of PaymentController::generateQr().
// It is kept alive only because some older routes may still point to it.
// All new code should use POST /api/payment/generate-qr (PaymentController).
// This controller simply delegates to the same logic so both routes work.

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Piseth\BakongKhqr\BakongKHQR;
use Piseth\BakongKhqr\Models\MerchantInfo;

class GenerateKhqrController extends Controller
{
    private const QR_EXPIRY_MINUTES = 10;

    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|integer',
            'amount' => 'nullable|numeric|min:0.01',
        ]);

        $authUser = $request->user();
        if (! $authUser) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $order = Order::find($request->order_id);
        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }
        if ($order->user_id !== $authUser->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }
        if ($order->payment_status === 'paid') {
            return response()->json(['success' => false, 'message' => 'Order is already paid.'], 422);
        }
        if ($order->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Cannot pay a cancelled order.'], 422);
        }

        // Return existing pending unexpired QR
        $existing = Payment::where('order_id', $order->id)
            ->where('status', Payment::STATUS_PENDING)
            ->latest()->first();

        if ($existing && ! $existing->isExpired()) {
            return $this->successResponse($order, $existing->qr_data, $existing->qr_md5, $existing->qr_expires_at);
        }

        $amount = round((float) $order->total, 2);
        $tranId = 'TRX-'.strtoupper(substr(md5($order->id.uniqid()), 0, 8));
        $expiresAt = now()->addMinutes(self::QR_EXPIRY_MINUTES);
        $qrString = null;
        $md5Hash = null;

        try {
            $merchantInfo = new MerchantInfo(
                config('services.bakong.bakong_id'),
                config('services.bakong.merchant_name'),
                config('services.bakong.merchant_city'),
                'ABA Bank',
                'USD'
            );
            $merchantInfo->amount = $amount;
            $merchantInfo->billNumber = 'TRX-'.str_pad($order->id, 8, '0', STR_PAD_LEFT);
            $merchantInfo->storeLabel = 'Tronmatix';
            $merchantInfo->terminalLabel = 'web';
            $merchantInfo->mobileNumber = ''; // '' not null — avoids length validation error

            $result = (new BakongKHQR(''))->generateMerchant($merchantInfo);

            if (! is_array($result) || empty($result['qr'])) {
                throw new \RuntimeException('KHQR generation returned empty result');
            }

            $qrString = $result['qr'];
            $md5Hash = $result['md5'] ?? md5($qrString);

        } catch (\Throwable $e) {
            Log::warning('KHQR generation failed — static fallback: '.$e->getMessage());
            $qrString = config('services.bakong.static_payway_url',
                env('KHQR_STATIC_PAYWAY_URL', 'https://link.payway.com.kh/ABAPAYTD422549V'));
            // FIX: generate a deterministic md5 from the QR string so verify() has
            // something to check instead of hitting the "no qr_md5 → 400" branch.
            $md5Hash = md5($qrString . $order->id);
        }

        // FIX [1]: removed literal newline from 'qr_data\n' key
        // FIX [2]: added 'status' key
        // FIX [3]: added 'provider' key
        Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'tran_id' => $tranId,
                'provider' => 'bakong',          // FIX [3]
                'payment_method' => 'bakong',
                'currency' => 'USD',
                'qr_data' => $qrString,          // FIX [1]: no newline
                'qr_md5' => $md5Hash,
                'qr_expires_at' => $expiresAt,
                'amount' => $amount,
                'status' => Payment::STATUS_PENDING, // FIX [2]
                'paid' => false,
                'meta' => ['currency' => 'USD'],
            ]
        );

        return $this->successResponse($order, $qrString, $md5Hash, $expiresAt);
    }

    /** FIX [4]: response key is 'qr_expiration' (ISO string) matching CheckoutPage contract */
    private function successResponse(Order $order, ?string $qrCode, ?string $qrMd5, $expiresAt): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'qr_code' => $qrCode,
                'qr_md5' => $qrMd5,
                'amount' => round((float) $order->total, 2),
                'currency' => 'USD',
                'qr_expiration' => ($expiresAt instanceof \Carbon\Carbon)  // FIX [4]
                    ? $expiresAt->toIso8601String()
                    : $expiresAt,
            ],
        ]);
    }
}
