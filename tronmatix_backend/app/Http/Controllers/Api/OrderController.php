<?php

// app/Http/Controllers/Api/OrderController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\TelegramService;      // Bot 1 — admin/owner alerts
use App\Services\TelegramUserService;  // Bot 2 — user notifications  ← ADD
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    // ── List current user's orders ────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $orders = Order::with(['items', 'location'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'total' => $orders->total(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }

    // ── Show single order ─────────────────────────────────────────────────────
    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        return response()->json(['success' => true, 'data' => $order->load(['items', 'location'])]);
    }

    // ── Create order ──────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => ['required', 'integer', 'exists:products,id'],
            'items.*.qty'            => ['required', 'integer', 'min:1', 'max:999'],
            'location_id'            => ['nullable', 'integer', 'exists:user_locations,id'],
            'location'               => ['required', 'array'],
            'location.name'          => ['required', 'string', 'max:255'],
            'location.phone'         => ['required', 'string', 'max:50'],
            // address is required for delivery, optional for pickup
            'location.address'       => ['nullable', 'string', 'max:500'],
            'location.city'          => ['nullable', 'string', 'max:100'],
            'location.country'       => ['nullable', 'string', 'max:100'],
            'location.note'          => ['nullable', 'string', 'max:500'],
            'payment_method'         => ['required', 'string', 'in:cash,bakong,card'],
            'discount_code'          => ['nullable', 'string', 'max:50'],
            'discount_amount'        => ['nullable', 'numeric', 'min:0'],
            'delivery_date'          => ['nullable', 'date', 'after_or_equal:today'],
            'delivery_time_slot'     => ['nullable', 'string', 'max:50'],
            'delivery_lat'           => ['nullable', 'numeric'],
            'delivery_lng'           => ['nullable', 'numeric'],
            'delivery_map_address'   => ['nullable', 'string', 'max:1000'],
            'fulfillment_type'       => ['nullable', 'in:delivery,pickup'],
        ]);

        $user = $request->user();

        // ── Pre-validate discount ─────────────────────────────────────────────
        $discount     = null;
        $discountCode = null;

        if (! empty($validated['discount_code'])) {
            $discount = Discount::where('code', strtoupper($validated['discount_code']))
                ->where('is_active', true)->first();

            if (! $discount || ($discount->expires_at && $discount->expires_at->isPast())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired discount code.',
                    'errors'  => ['discount_code' => ['Invalid or expired discount code.']],
                ], 422);
            }
            if ($discount->max_uses && $discount->used_count >= $discount->max_uses) {
                return response()->json([
                    'success' => false,
                    'message' => 'Discount code usage limit reached.',
                    'errors'  => ['discount_code' => ['Discount code usage limit reached.']],
                ], 422);
            }
            $discountCode = $discount->code;
        }

        try {
            $order = DB::transaction(function () use ($validated, $user, $discount, $discountCode) {

                $productIds = collect($validated['items'])->pluck('product_id');
                $products   = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

                foreach ($validated['items'] as $item) {
                    $product = $products->get($item['product_id']);
                    if ($product->stock !== null && $product->stock < $item['qty']) {
                        throw new \RuntimeException(
                            "Insufficient stock for \"{$product->name}\". Only {$product->stock} left."
                        );
                    }
                }

                $subtotal       = collect($validated['items'])
                    ->sum(fn ($i) => $products[$i['product_id']]->price * $i['qty']);
                $discountAmount = 0;
                $discountId     = null;

                if ($discount) {
                    $lockedDiscount = Discount::where('id', $discount->id)->lockForUpdate()->first();

                    if ($lockedDiscount->max_uses && $lockedDiscount->used_count >= $lockedDiscount->max_uses) {
                        throw new \RuntimeException('This discount code has just reached its usage limit.');
                    }
                    if ($discount->min_order && $subtotal < $discount->min_order) {
                        throw new \RuntimeException(
                            "Minimum order of \${$discount->min_order} required for this discount."
                        );
                    }

                    $discountAmount = $discount->type === 'percentage'
                        ? round($subtotal * ($discount->value / 100), 2)
                        : min($discount->value, $subtotal);

                    $discountId = $discount->id;

                } elseif (! empty($validated['discount_amount']) && (float) $validated['discount_amount'] > 0) {
                    $discountAmount = min((float) $validated['discount_amount'], $subtotal);
                }

                $total = max(0, $subtotal - $discountAmount);

                // ── Resolve fulfillment type ──────────────────────────────────────────
                $fulfillmentType = $validated['fulfillment_type'] ?? 'delivery';
                $isPickup        = $fulfillmentType === 'pickup';

                // ── Validate delivery address (pickup doesn't need one) ────────────────
                if (! $isPickup && empty($validated['location']['address'])) {
                    throw new \RuntimeException('Delivery address is required for delivery orders.');
                }

                // ✅ FIX: resolve saved location and build shipping snapshot with lat/lng
                // Priority: saved location FK → manual map pin from request
                $resolvedLocationId = null;
                $shippingSnapshot   = $validated['location']; // default: manual fields only

                if ($isPickup) {
                    // Pickup: store name + phone only, inject store address
                    $shippingSnapshot = [
                        'name'        => $validated['location']['name']  ?? '',
                        'phone'       => $validated['location']['phone'] ?? '',
                        'address'     => 'Store Pickup — Tronmatix Computer',
                        'city'        => '',
                        'note'        => $validated['location']['note']  ?? '',
                        'lat'         => null,
                        'lng'         => null,
                        'map_address' => null,
                    ];
                } elseif (! empty($validated['location_id'])) {
                    $savedLoc = \App\Models\UserLocation::where('user_id', $user->id)
                        ->find($validated['location_id']);

                    if ($savedLoc) {
                        $resolvedLocationId = $savedLoc->id;
                        // toShippingArray() now includes lat/lng/map_address (see UserLocation fix)
                        $shippingSnapshot = $savedLoc->toShippingArray();
                    }
                }

                // If saved location had no pin, fall back to manual map pin from request
                if (! $isPickup && empty($shippingSnapshot['lat']) && ! empty($validated['delivery_lat'])) {
                    $shippingSnapshot['lat']         = $validated['delivery_lat'];
                    $shippingSnapshot['lng']         = $validated['delivery_lng'] ?? null;
                    $shippingSnapshot['map_address'] = $validated['delivery_map_address'] ?? null;
                }

                $order = Order::create([
                    'user_id'            => $user->id,
                    'payment_method'     => $validated['payment_method'],
                    'subtotal'           => $subtotal,
                    'discount_code'      => $discountCode,
                    'discount_id'        => $discountId,
                    'discount_amount'    => $discountAmount,
                    'tax'                => 0,
                    'delivery'           => 0,
                    'total'              => $total,
                    'location_id'        => $resolvedLocationId, // ✅ verified FK
                    'shipping'           => $shippingSnapshot,   // ✅ snapshot includes lat/lng
                    'status'             => 'confirmed',
                    'delivery_date'      => $validated['delivery_date']      ?? null,
                    'delivery_time_slot' => $validated['delivery_time_slot'] ?? null,
                    'fulfillment_type'   => $fulfillmentType, // ✅ 'delivery' | 'pickup'
                ]);

                foreach ($validated['items'] as $item) {
                    $product = $products[$item['product_id']];
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $product->id,
                        'name'       => $product->name,
                        'price'      => $product->price,
                        'qty'        => $item['qty'],
                        'image'      => $product->image,
                    ]);
                    if ($product->stock !== null) {
                        $product->decrement('stock', $item['qty']);
                    }
                }

                if ($discountId) {
                    Discount::where('id', $discountId)->increment('used_count');
                }

                return $order;
            });

        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => ['items' => [$e->getMessage()]],
            ], 422);
        } catch (\Throwable $e) {
            // DB/PDO/query errors — return clean JSON instead of 500 HTML
            Log::error('[OrderController] Transaction error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again.',
            ], 500);
        }

        $order->load(['items', 'user', 'location']);

        // ISSUE 1 FIX: Bot 1 (admin) — send full receipt to shop owner channel
        try {
            app(TelegramService::class)->sendReceipt($order);
        } catch (\Throwable $e) {
            Log::warning('[Bot1] Admin receipt failed: ' . $e->getMessage());
        }

        // ISSUE 1 FIX: Bot 2 (user) — send receipt to customer's Telegram (if connected)
        try {
            app(TelegramUserService::class)->onOrderPlaced($order);
        } catch (\Throwable $e) {
            Log::warning('[Bot2] User receipt failed: ' . $e->getMessage());
        }

        return response()->json([
            'success'          => true,
            'order_id'         => $order->order_id,
            'id'               => $order->id,
            'fulfillment_type' => $order->fulfillment_type,  // ✅ frontend needs this for receipt UI
            'items'            => $order->items,
            'location'         => $order->shipping,
            'location_id'      => $order->location_id,
            'subtotal'         => $order->subtotal,
            'discount_code'    => $order->discount_code,
            'discount_amount'  => $order->discount_amount,
            'tax'              => $order->tax,
            'delivery'         => $order->delivery,
            'total'            => $order->total,
            'payment_method'   => $order->payment_method,
            'status'           => $order->status,
            'delivery_lat'         => $order->shipping['lat']         ?? null,
            'delivery_lng'         => $order->shipping['lng']         ?? null,
            'delivery_map_address' => $order->shipping['map_address'] ?? null,
            'created_at'           => $order->created_at,
        ], 201);
    }

    // ── Cancel own order ──────────────────────────────────────────────────────
    public function cancel(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }
        if (! in_array($order->status, ['confirmed', 'pending'])) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be cancelled at status: ' . $order->status,
            ], 422);
        }

        $order->update(['status' => 'cancelled']);

        // Load both relations — onOrderCancelled() needs items for itemSummaryLine()
        $order->load(['user', 'items']);

        // Bot 1 (admin) — alert owner about cancellation
        try {
            app(TelegramService::class)->sendAlert(
                "🚫 *Order Cancelled by Customer*\n\n" .
                "📦 Order: `#{$order->order_id}`\n" .
                "💰 Amount: \${$order->total}\n" .
                '👤 ' . ($order->user?->username ?? 'Guest') . "\n" .
                '🕐 ' . now()->format('d M Y, H:i')
            );
        } catch (\Throwable $e) {
            Log::warning('[Bot1] Cancel alert failed: ' . $e->getMessage());
        }

        // ISSUE 2 FIX: Bot 2 (user) — notify customer their order was cancelled
        try {
            app(TelegramUserService::class)->onOrderCancelled($order);
        } catch (\Throwable $e) {
            Log::warning('[Bot2] User cancel notification failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Order cancelled successfully.']);
    }

    // ── Delete own cancelled order ────────────────────────────────────────────
    public function destroy(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }
        if ($order->status !== 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Only cancelled orders can be deleted.'], 422);
        }

        $order->items()->delete();
        $order->delete();

        return response()->json(['success' => true, 'message' => 'Order deleted.']);
    }

    // ── Confirm delivery ──────────────────────────────────────────────────────
    public function confirmDelivery(Request $request, Order $order): JsonResponse
    {
        // BUG 1 FIX: was missing — any user could confirm any order
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        if ($order->delivery_confirmed_at) {
            return response()->json(['success' => false, 'message' => 'Already confirmed.'], 422);
        }

        $order->update(['status' => 'delivered', 'delivery_confirmed_at' => now()]);
        // BUG 3 FIX: load items too — onOrderDelivered() needs itemSummaryLine()
        $order->load(['user', 'items']);

        // Bot 1 (admin) — confirm delivery to owner channel
        try {
            app(TelegramService::class)->sendDeliveryConfirmed($order);
        } catch (\Throwable $e) {
            Log::warning('[Bot1] Delivery confirm failed: ' . $e->getMessage());
        }

        // ISSUE 3 FIX: Bot 2 (user) — notify customer their order was delivered
        try {
            app(TelegramUserService::class)->onOrderDelivered($order);
        } catch (\Throwable $e) {
            Log::warning('[Bot2] User delivery notification failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'data' => $order]);
    }
}
