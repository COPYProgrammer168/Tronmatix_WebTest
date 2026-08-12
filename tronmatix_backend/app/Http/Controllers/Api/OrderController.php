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
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    // ── Check if user is staff ─────────────────────────────────────────────────
    private function isStaff($user): bool
    {
        return $user instanceof \App\Models\Staff
            || in_array($user->role ?? '', ['admin', 'superadmin', 'editor', 'seller', 'delivery', 'developer']);
    }

    // ── List orders ───────────────────────────────────────────────────────────
    // Customers see only their own orders.
    // Staff roles see ALL orders.
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $isStaff = $this->isStaff($user);

        $orders = Order::with(['items', 'user', 'location', 'deliveryProvider.zones']);

        if (! $isStaff) {
            $orders->where('user_id', $user->id);
        }

        $perPage = min((int) $request->input('per_page', 20), 200);
        $orders = $orders->latest()->paginate($perPage);

        $data = collect($orders->items())->map(fn ($o) => $this->serializeOrder($o));

        return response()->json([
            'success' => true,
            'data' => $data,
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
        $user = $request->user();

        // Staff can view any order; customers can only view their own
        if (! $this->isStaff($user) && $order->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $order->load(['items', 'user', 'location', 'payments', 'deliveryProvider.zones']);

        return response()->json([
            'success' => true,
            'data' => $this->serializeOrder($order),
        ]);
    }

    /**
     * Standardize the order shape sent to the frontend, including the
     * zone-aware delivery provider details (resolved server-side).
     */
    private function serializeOrder(Order $order)
    {
        $data                             = $order->toArray();
        $data['delivery_zone']            = $order->delivery_zone;
        $data['delivery_provider_details']= $order->delivery_provider_details;
        return $data;
    }

    /**
     * Derive the delivery zone ('phnom_penh' | 'province') from the customer's
     * map pin via the distance-based DeliveryFeeCalculator. Used when the
     * frontend didn't send an explicit delivery_zone. Returns null on failure.
     */
    private function resolveDeliveryZone(array $validated, array $shippingSnapshot): ?string
    {
        $cLat = $shippingSnapshot['lat'] ?? $validated['delivery_lat'] ?? null;
        $cLng = $shippingSnapshot['lng'] ?? $validated['delivery_lng'] ?? null;
        if ($cLat === null || $cLng === null) {
            return null;
        }

        try {
            $feeResult = app(\App\Services\DeliveryFeeCalculator::class)
                ->calculate((float) $cLat, (float) $cLng);
            return str_contains(strtolower($feeResult['zone_name'] ?? ''), 'phnom penh')
                ? 'phnom_penh'
                : 'province';
        } catch (\Throwable $e) {
            Log::warning('[Order] Delivery zone resolution failed: ' . $e->getMessage());
            return null;
        }
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
            'province_id'            => ['nullable', 'integer', 'exists:provinces,id'],
            'delivery_provider_id'   => ['nullable', 'integer', 'exists:delivery_providers,id'],
            'delivery_phone_verified'=> ['nullable', 'boolean'],
            'delivery_zone'          => ['nullable', 'in:phnom_penh,province'],
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

        // ── Validate delivery location (pickup doesn't need an address) ──────
        // Done BEFORE the transaction so a missing address surfaces as a clean
        // 422 under errors.location.address (not the generic errors.items catch).
        $preFulfillment = $validated['fulfillment_type'] ?? 'delivery';
        if ($preFulfillment !== 'pickup') {
            $preAddress = trim((string) ($validated['location']['address'] ?? ''));

            if (! empty($validated['location_id'])) {
                // Saved-location delivery — check the saved record's address.
                $savedLoc = \App\Models\UserLocation::where('user_id', $user->id)
                    ->find($validated['location_id']);
                if (! $savedLoc || empty(trim((string) ($savedLoc->address ?? '')))) {
                    throw ValidationException::withMessages([
                        'location.address' => 'Delivery address is required for delivery orders.',
                    ]);
                }
            } elseif ($preAddress === '') {
                // Manual delivery — the payload must carry an address.
                throw ValidationException::withMessages([
                    'location.address' => 'Delivery address is required for delivery orders.',
                ]);
            }
        }

        try {
            $order = DB::transaction(function () use ($validated, $user, $discount, $discountCode) {

                // ── Pre-check pass (UX nicety only — the real guarantee comes from
                //    the locked sell() calls in stockOutOrder below). Merges + sorts
                //    line items and reads stock in one batched query.
                $mergedItems = app(\App\Services\OrderStockService::class)->mergeAndSortForValidation($validated['items']);
                $productIds  = array_column($mergedItems, 'product_id');
                $products    = Product::whereIn('id', $productIds)->get()->keyBy('id');

                foreach ($mergedItems as $item) {
                    $product = $products->get($item['product_id']);
                    if ($product && $product->stock !== null && $product->stock < $item['qty']) {
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
                // Common path already caught pre-transaction as errors.location.address;
                // this is a belt-and-suspenders guard for edge cases.
                if (! $isPickup && empty(trim((string) ($validated['location']['address'] ?? '')))) {
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

                // Enrich shipping snapshot with province/city so Telegram can show it
                if (!empty($validated['province_id'])) {
                    $prov = \App\Models\Province::find($validated['province_id']);
                    if ($prov) {
                        $shippingSnapshot['province']      = $prov->name_en ?? $prov->name_kh ?? '';
                        $shippingSnapshot['province_id']   = (int) $prov->id;
                        // Backfill city from province name if customer didn't type one
                        if (empty($shippingSnapshot['city'])) {
                            $shippingSnapshot['city'] = $prov->name_en ?? $prov->name_kh ?? '';
                        }
                    }
                }

                $shippingSnapshot['delivery_phone_verified'] = $validated['delivery_phone_verified'] ?? false;

                // ── Resolve the delivery zone (phnom_penh | province) ────────────────
                // The customer picks a province (→ zone) in checkout; prefer that so the
                // persisted provider-zone row matches what the customer saw. Fall back to
                // the distance-based DeliveryFeeCalculator (lat/lng) for the fee/ETA.
                $deliveryZone = null;
                if (! $isPickup) {
                    $deliveryZone = $validated['delivery_zone']
                        ?? $this->resolveDeliveryZone($validated, $shippingSnapshot);
                }

                // ── Delivery fee logic ────────────────────────────────────────────────
                // Resolve the per-zone provider fee (delivery_provider_zones.fee). The
                // legacy flat `delivery_providers.fee` is always NULL (admin form stores
                // per-zone fees in the child table), so we must read the zone row here or
                // the fee would always be 0. NULL zone fee = negotiable → fee stays 0.
                $deliveryFee = 0;
                if (! $isPickup && ! empty($validated['delivery_provider_id'])) {
                    $provider = \App\Models\DeliveryProvider::with('zones')->find($validated['delivery_provider_id']);
                    if ($provider) {
                        $zone = $deliveryZone ?? 'phnom_penh';
                        $zd   = $provider->zones->firstWhere('zone', $zone);
                        if ($zd && $zd->fee !== null) {
                            $deliveryFee = (float) $zd->fee;
                        } elseif ($provider->fee !== null) {
                            $deliveryFee = (float) $provider->fee; // legacy flat fallback
                        }
                    }
                }
                $total += $deliveryFee;

                $order = Order::create([
                    'user_id'            => $user->id,
                    'payment_method'     => $validated['payment_method'],
                    'subtotal'           => $subtotal,
                    'discount_code'      => $discountCode,
                    'discount_id'        => $discountId,
                    'discount_amount'    => $discountAmount,
                    'tax'                => 0,
                    'delivery'           => $deliveryFee,
                    'total'              => $total,
                    'location_id'        => $resolvedLocationId, // ✅ verified FK
                    'province_id'        => $validated['province_id'] ?? null,
                    'delivery_provider_id' => $validated['delivery_provider_id'] ?? null,
                    'delivery_zone'      => $deliveryZone, // ✅ persisted zone for zone-aware ETA/fee
                    'shipping'           => $shippingSnapshot,   // ✅ snapshot includes lat/lng
                    // Bakong/KHQR orders start as pending — promoted to confirmed
                    // when payment is verified (CheckPaymentController or webhook).
                    // Cash/Card orders start as confirmed immediately.
                    'status'             => $validated['payment_method'] === 'bakong' ? 'pending' : 'confirmed',
                    'delivery_date'      => $validated['delivery_date']      ?? null,
                    'delivery_time_slot' => $validated['delivery_time_slot'] ?? null,
                    'fulfillment_type'   => $fulfillmentType, // ✅ 'delivery' | 'pickup'
                ]);

                foreach ($validated['items'] as $item) {
                    $product = $products[$item['product_id']];

                    $warrantyStart = null;
                    $warrantyEnd = null;

                    if (!empty($product->warranty)) {
                        // Assume warranty format: "2 years" or "12 months"
                        $warrantyStart = now();
                        $warrantyEnd = now();

                        if (str_contains(strtolower($product->warranty), 'year')) {
                            $years = (int) filter_var($product->warranty, FILTER_SANITIZE_NUMBER_INT);
                            $warrantyEnd = now()->addYears($years);
                        } elseif (str_contains(strtolower($product->warranty), 'month')) {
                            $months = (int) filter_var($product->warranty, FILTER_SANITIZE_NUMBER_INT);
                            $warrantyEnd = now()->addMonths($months);
                        }
                    }

                    OrderItem::create([
                        'order_id'       => $order->id,
                        'product_id'     => $product->id,
                        'name'           => $product->name,
                        'price'          => $product->price,
                        'qty'            => $item['qty'],
                        'image'          => $product->image,
                        'brand'          => $product->brand,
                        'warranty_start' => $warrantyStart,
                        'warranty_end'   => $warrantyEnd,
                    ]);
                }

                // ── Stock-out: merged + product_id-sorted sell() calls inside an
                //    outer transaction, with the order as the reference. Replaces the
                //    old per-item decrement. Idempotent (double-processing guard).
                app(\App\Services\OrderStockService::class)->stockOutOrder($order, $validated['items']);

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

        // Telegram alerts — only for non-bakong payments.
        // For Bakong/KHQR, alerts are sent AFTER payment confirmation
        // via CheckPaymentController (webhook or polling).
        if ($order->payment_method !== 'bakong') {
            // Bot 1 (admin) — send full receipt to shop owner channel
            try {
                app(TelegramService::class)->sendReceipt($order);
            } catch (\Throwable $e) {
                Log::warning('[Bot1] Admin receipt failed: ' . $e->getMessage());
            }

            // Bot 2 (user) — send receipt to customer's Telegram (if connected)
            try {
                app(TelegramUserService::class)->onOrderPlaced($order);
            } catch (\Throwable $e) {
                Log::warning('[Bot2] User receipt failed: ' . $e->getMessage());
            }
        } else {
            Log::info('Bakong order placed — Telegram alerts deferred until payment confirmation', [
                'order_id' => $order->id,
            ]);
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
            'payment_status'   => $order->payment_status,
            'status'           => $order->status,
            'delivery_zone'           => $order->delivery_zone,
            'delivery_provider_id'    => $order->delivery_provider_id,
            'delivery_provider_details' => $order->delivery_provider_details,
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

        DB::transaction(function () use ($order) {
            $order->update(['status' => 'cancelled']);
            // Stock-back-in via the ledger — reverses each not-yet-reversed
            // movement referencing this order, inside the same outer transaction.
            app(\App\Services\OrderStockService::class)->restoreOrderStock($order);
        });

        \App\Services\ActivityLogger::log([
            'action'      => 'order_cancelled',
            'entity_type' => 'Order',
            'entity_id'   => $order->id,
            'entity_name' => $order->order_id,
            'details'     => ['cancelled_by' => $user->name ?? 'Customer'],
        ], $request);

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

        return response()->json(['success' => true, 'message' => 'Order cancelled successfully and stock restored.']);
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

    // ── Staff: update order status ────────────────────────────────────────────
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        if (! $this->isStaff($user)) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        $order->update(['status' => $newStatus]);
        $order->load(['items', 'user']);

        \App\Services\ActivityLogger::orderStatusChange($order, $oldStatus, $newStatus, $request);

        // Bot alerts (same as backend Blade dashboard)
        try {
            app(TelegramService::class)->sendAlert(
                "📋 *Order Status Updated*\n\n" .
                "📦 Order: `#{$order->order_id}`\n" .
                "👤 " . ($order->user?->username ?? 'Guest') . "\n" .
                "🔄 {$oldStatus} → *{$newStatus}*\n" .
                "🕐 " . now()->format('d M Y, H:i')
            );
        } catch (\Throwable $e) {
            Log::warning('[Bot1] Status alert failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'data' => $order]);
    }

    // ── Staff: verify payment ─────────────────────────────────────────────────
    public function verifyPayment(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        if (! $this->isStaff($user)) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $order->update([
            'payment_status' => 'paid',
            'status'         => $order->status === 'pending' ? 'confirmed' : $order->status,
            'payment_ref'    => 'Manual Verification',
        ]);

        // ── Stock management: pending Bakong/KHQR orders were not stocked out at
        //    placement; stock leaves the shelf on confirmation. Idempotent.
        if ($order->status === 'confirmed') {
            try {
                app(\App\Services\OrderStockService::class)->stockOutOrder($order, $order->items);
            } catch (\Throwable $e) {
                Log::warning('[Stock] Stock-out on API payment verify failed: ' . $e->getMessage());
            }
        }

        $order->load(['items', 'user']);

        \App\Services\ActivityLogger::paymentVerified($order, $request);

        // Bot 1 (admin)
        try {
            app(TelegramService::class)->sendPaymentConfirmed($order, 'Manual Verification');
        } catch (\Throwable $e) {
            Log::warning('[Bot1] Payment verify alert failed: ' . $e->getMessage());
        }

        // Bot 2 (user)
        try {
            app(TelegramUserService::class)->onPaymentConfirmed($order, 'Manual Verification');
        } catch (\Throwable $e) {
            Log::warning('[Bot2] Payment verify user alert failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'data' => $order]);
    }

    // ── Staff: confirm delivery ───────────────────────────────────────────────
    public function staffConfirmDelivery(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        if (! $this->isStaff($user)) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        if (! in_array($order->status, ['confirmed', 'processing', 'shipped'])) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be marked as delivered from its current status.',
            ], 422);
        }

        $order->update(['status' => 'delivered', 'delivery_confirmed_at' => now()]);
        $order->load(['user', 'items']);

        \App\Services\ActivityLogger::deliveryConfirmed($order, $user->name ?? 'Staff', $request);

        try {
            app(TelegramService::class)->sendDeliveryConfirmed($order);
        } catch (\Throwable $e) {
            Log::warning('[Bot1] Staff delivery confirm failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'data' => $order]);
    }
}
