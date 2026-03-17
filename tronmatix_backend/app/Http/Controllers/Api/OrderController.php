<?php

// app/Http/Controllers/Api/OrderController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\TelegramService;
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

        // FIX [4]: eager-load 'location' alongside items
        return response()->json(['success' => true, 'data' => $order->load(['items', 'location'])]);
    }

    // ── Create order ──────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:999'],
            'location_id' => ['nullable', 'integer', 'exists:user_locations,id'],
            'location' => ['required', 'array'],
            'location.name' => ['required', 'string', 'max:255'],
            'location.phone' => ['required', 'string', 'max:50'],
            'location.address' => ['required', 'string', 'max:500'],
            'location.city' => ['nullable', 'string', 'max:100'],
            'location.country' => ['nullable', 'string', 'max:100'],  // FIX [2]
            'location.note' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'string', 'in:cash,bakong,card'],
            'discount_code'   => ['nullable', 'string', 'max:50'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],  // frontend-calculated public/sitewide discount
            'delivery_date'   => ['nullable', 'date', 'after_or_equal:today'],
            'delivery_time_slot' => ['nullable', 'string', 'max:50'],
        ]);

        $user = $request->user();

        // ── Pre-validate discount (read-only — no lock needed yet) ────────────
        $discount = null;
        $discountAmount = 0;
        $discountCode = null;

        if (! empty($validated['discount_code'])) {
            $discount = Discount::where('code', strtoupper($validated['discount_code']))
                ->where('is_active', true)->first();

            if (! $discount || ($discount->expires_at && $discount->expires_at->isPast())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired discount code.',
                    'errors' => ['discount_code' => ['Invalid or expired discount code.']],
                ], 422);
            }
            if ($discount->max_uses && $discount->used_count >= $discount->max_uses) {
                return response()->json([
                    'success' => false,
                    'message' => 'Discount code usage limit reached.',
                    'errors' => ['discount_code' => ['Discount code usage limit reached.']],
                ], 422);
            }
            $discountCode = $discount->code;
        }

        try {
            $order = DB::transaction(function () use ($validated, $user, $discount, $discountCode) {

                // Lock products for stock check
                $productIds = collect($validated['items'])->pluck('product_id');
                $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

                foreach ($validated['items'] as $item) {
                    $product = $products->get($item['product_id']);
                    if ($product->stock !== null && $product->stock < $item['qty']) {
                        throw new \RuntimeException(
                            "Insufficient stock for \"{$product->name}\". Only {$product->stock} left."
                        );
                    }
                }

                $subtotal = collect($validated['items'])
                    ->sum(fn ($i) => $products[$i['product_id']]->price * $i['qty']);

                $discountAmount = 0;
                $discountId = null;

                if ($discount) {
                    // Re-lock discount for max_uses and min_order check inside transaction
                    $lockedDiscount = Discount::where('id', $discount->id)->lockForUpdate()->first();

                    if ($lockedDiscount->max_uses && $lockedDiscount->used_count >= $lockedDiscount->max_uses) {
                        throw new \RuntimeException('This discount code has just reached its usage limit.');
                    }
                    // FIX [5]: min_order check moved inside transaction with real subtotal
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
                    // No code entered — frontend sent a pre-calculated public/sitewide discount.
                    // calcDiscount() on the frontend already computed this correctly via DiscountContext.
                    // Cap at subtotal to prevent abuse.
                    $discountAmount = min((float) $validated['discount_amount'], $subtotal);
                }

                $total = max(0, $subtotal - $discountAmount);

                // FIX [1]: location_id now saved
                // FIX [3]: removed redundant order_id — Order::boot() generates it on creating
                $order = Order::create([
                    'user_id' => $user->id,
                    'payment_method' => $validated['payment_method'],
                    'subtotal' => $subtotal,
                    'discount_code' => $discountCode,
                    'discount_id' => $discountId,
                    'discount_amount' => $discountAmount,
                    'tax' => 0,
                    'delivery' => 0,
                    'total' => $total,
                    'location_id' => $validated['location_id'] ?? null,  // FIX [1]
                    'shipping' => $validated['location'],
                    'status' => 'confirmed',
                    'delivery_date' => $validated['delivery_date'] ?? null,
                    'delivery_time_slot' => $validated['delivery_time_slot'] ?? null,
                ]);

                foreach ($validated['items'] as $item) {
                    $product = $products[$item['product_id']];
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'price' => $product->price,
                        'qty' => $item['qty'],
                        'image' => $product->image,
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
                'errors' => ['items' => [$e->getMessage()]],
            ], 422);
        }

        $order->load(['items', 'user', 'location']);

        try {
            app(TelegramService::class)->sendReceipt($order);
        } catch (\Throwable $e) {
            Log::warning('Telegram notification failed: '.$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'order_id' => $order->order_id,
            'id' => $order->id,
            'items' => $order->items,
            'location' => $order->shipping,
            'subtotal' => $order->subtotal,
            'discount_code' => $order->discount_code,
            'discount_amount' => $order->discount_amount,
            'tax' => $order->tax,
            'delivery' => $order->delivery,
            'total' => $order->total,
            'payment_method' => $order->payment_method,
            'status' => $order->status,
            'created_at' => $order->created_at,
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
                'message' => 'Order cannot be cancelled at status: '.$order->status,
            ], 422);
        }

        $order->update(['status' => 'cancelled']);

        try {
            app(TelegramService::class)->sendAlert(
                "🚫 *Order Cancelled by Customer*\n\n".
                "📦 Order: `#{$order->order_id}`\n".
                "💰 Amount: \${$order->total}\n".
                '👤 '.($order->user?->username ?? 'Guest')."\n".
                '🕐 '.now()->format('d M Y, H:i')
            );
        } catch (\Throwable $e) {
            Log::warning('Telegram cancel alert failed: '.$e->getMessage());
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

    // ── Admin: confirm delivery ───────────────────────────────────────────────
    public function confirmDelivery(Order $order): JsonResponse
    {
        if ($order->delivery_confirmed_at) {
            return response()->json(['success' => false, 'message' => 'Already confirmed.'], 422);
        }
        $order->update(['status' => 'delivered', 'delivery_confirmed_at' => now()]);

        try {
            app(TelegramService::class)->sendDeliveryConfirmed($order->load('user'));
        } catch (\Throwable $e) {
            Log::warning('Telegram delivery confirm failed: '.$e->getMessage());
        }

        return response()->json(['success' => true, 'data' => $order]);
    }
}
