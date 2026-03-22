<?php

// app/Http/Controllers/Api/OrderController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\TelegramService;      // Bot 1 — admin/owner alerts
use App\Services\TelegramUserService;  // Bot 2 — user notifications
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::with(['items', 'location'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $orders->items(),
            'meta'    => [
                'total'        => $orders->total(),
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }
        return response()->json(['success' => true, 'data' => $order->load(['items', 'location'])]);
    }

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
            'location.address'       => ['required', 'string', 'max:500'],
            'location.city'          => ['nullable', 'string', 'max:100'],
            'location.country'       => ['nullable', 'string', 'max:100'],
            'location.note'          => ['nullable', 'string', 'max:500'],
            'payment_method'         => ['required', 'string', 'in:cash,bakong,card'],
            'discount_code'          => ['nullable', 'string', 'max:50'],
            'discount_amount'        => ['nullable', 'numeric', 'min:0'],
            'delivery_date'          => ['nullable', 'date', 'after_or_equal:today'],
            'delivery_time_slot'     => ['nullable', 'string', 'max:50'],
        ]);

        $user     = $request->user();
        $discount = null;
        $discountCode = null;

        if (! empty($validated['discount_code'])) {
            $discount = Discount::where('code', strtoupper($validated['discount_code']))
                ->where('is_active', true)->first();

            if (! $discount || ($discount->expires_at && $discount->expires_at->isPast())) {
                return response()->json(['success' => false, 'message' => 'Invalid or expired discount code.',
                    'errors' => ['discount_code' => ['Invalid or expired discount code.']]], 422);
            }
            if ($discount->max_uses && $discount->used_count >= $discount->max_uses) {
                return response()->json(['success' => false, 'message' => 'Discount code usage limit reached.',
                    'errors' => ['discount_code' => ['Discount code usage limit reached.']]], 422);
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
                        throw new \RuntimeException("Insufficient stock for \"{$product->name}\". Only {$product->stock} left.");
                    }
                }

                $subtotal       = collect($validated['items'])->sum(fn($i) => $products[$i['product_id']]->price * $i['qty']);
                $discountAmount = 0;
                $discountId     = null;

                if ($discount) {
                    $locked = Discount::where('id', $discount->id)->lockForUpdate()->first();
                    if ($locked->max_uses && $locked->used_count >= $locked->max_uses) {
                        throw new \RuntimeException('This discount code has just reached its usage limit.');
                    }
                    if ($discount->min_order && $subtotal < $discount->min_order) {
                        throw new \RuntimeException("Minimum order of \${$discount->min_order} required for this discount.");
                    }
                    $discountAmount = $discount->type === 'percentage'
                        ? round($subtotal * ($discount->value / 100), 2)
                        : min($discount->value, $subtotal);
                    $discountId = $discount->id;
                } elseif (! empty($validated['discount_amount']) && (float) $validated['discount_amount'] > 0) {
                    $discountAmount = min((float) $validated['discount_amount'], $subtotal);
                }

                $total = max(0, $subtotal - $discountAmount);

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
                    'location_id'        => $validated['location_id'] ?? null,
                    'shipping'           => $validated['location'],
                    'status'             => 'confirmed',
                    'delivery_date'      => $validated['delivery_date'] ?? null,
                    'delivery_time_slot' => $validated['delivery_time_slot'] ?? null,
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
            return response()->json(['success' => false, 'message' => $e->getMessage(),
                'errors' => ['items' => [$e->getMessage()]]], 422);
        } catch (\Throwable $e) {
            Log::error('[OrderController] Transaction error: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'An unexpected error occurred.'], 500);
        }

        $order->load(['items', 'user', 'location']);

        // Bot 1 — admin receipt
        try { app(TelegramService::class)->sendReceipt($order); }
        catch (\Throwable $e) { Log::warning('[Bot1] Admin receipt failed: '.$e->getMessage()); }

        // Bot 2 — user receipt (FIX: was missing in original)
        try { app(TelegramUserService::class)->onOrderPlaced($order); }
        catch (\Throwable $e) { Log::warning('[Bot2] User receipt failed: '.$e->getMessage()); }

        return response()->json([
            'success'         => true,
            'order_id'        => $order->order_id,
            'id'              => $order->id,
            'items'           => $order->items,
            'location'        => $order->shipping,
            'subtotal'        => $order->subtotal,
            'discount_code'   => $order->discount_code,
            'discount_amount' => $order->discount_amount,
            'tax'             => $order->tax,
            'delivery'        => $order->delivery,
            'total'           => $order->total,
            'payment_method'  => $order->payment_method,
            'status'          => $order->status,
            'created_at'      => $order->created_at,
        ], 201);
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }
        if (! in_array($order->status, ['confirmed', 'pending'])) {
            return response()->json(['success' => false, 'message' => 'Order cannot be cancelled at status: '.$order->status], 422);
        }

        $order->update(['status' => 'cancelled']);
        $order->load(['user', 'items']);

        try { app(TelegramService::class)->sendAlert(
            "🚫 *Order Cancelled by Customer*\n\n📦 Order: `#{$order->order_id}`\n💰 Amount: \${$order->total}\n👤 ".($order->user?->username ?? 'Guest')."\n🕐 ".now()->format('d M Y, H:i')
        ); } catch (\Throwable $e) { Log::warning('[Bot1] Cancel alert failed: '.$e->getMessage()); }

        // Bot 2 — notify user
        try { app(TelegramUserService::class)->onOrderCancelled($order); }
        catch (\Throwable $e) { Log::warning('[Bot2] User cancel failed: '.$e->getMessage()); }

        return response()->json(['success' => true, 'message' => 'Order cancelled successfully.']);
    }

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

    public function confirmDelivery(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }
        if ($order->delivery_confirmed_at) {
            return response()->json(['success' => false, 'message' => 'Already confirmed.'], 422);
        }

        $order->update(['status' => 'delivered', 'delivery_confirmed_at' => now()]);
        $order->load(['user', 'items']);

        try { app(TelegramService::class)->sendDeliveryConfirmed($order); }
        catch (\Throwable $e) { Log::warning('[Bot1] Delivery confirm failed: '.$e->getMessage()); }

        // Bot 2 — notify user
        try { app(TelegramUserService::class)->onOrderDelivered($order); }
        catch (\Throwable $e) { Log::warning('[Bot2] User delivery failed: '.$e->getMessage()); }

        return response()->json(['success' => true, 'data' => $order]);
    }
}
