<?php

// app/Services/OrderStockService.php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Coordinates stock movement with the order lifecycle — the glue between the
 * order controllers and StockService. Both the API OrderController and the
 * dashboard DashboardController call these methods instead of touching
 * `current_stock` directly.
 *
 * The non-obvious bits (all required by the stock-system design):
 *
 *  • Line items are MERGED by product_id first. Two separate cart entries for
 *    the same product must become one sell() call, or the same product row gets
 *    locked twice in one order and its history splits into two rows.
 *  • The merged items are SORTED by product_id before processing. Two orders
 *    sharing two products would otherwise deadlock when processed in opposite
 *    order (A locks 1 then waits on 2; B locks 2 then waits on 1).
 *  • The whole loop is one outer DB::transaction() so a multi-item order's
 *    stock-out is all-or-nothing. StockService::sell()'s own transaction nests
 *    as a savepoint.
 */
class OrderStockService
{
    public function __construct(
        private readonly StockService $stock,
    ) {}

    /**
     * Stock-out an order. Pass the ORDER as the reference on every sell() so the
     * double-processing guard and reverse-on-cancel can find the right movements.
     *
     * @param  array<int, array{product_id: int, qty: int}>|Collection  $lineItems
     * @return int  number of distinct products stocked out
     *
     * @throws InsufficientStockException  if any line exceeds available stock
     */
    public function stockOutOrder(Order $order, $lineItems): int
    {
        $merged = $this->mergeAndSort($lineItems);

        return DB::transaction(function () use ($order, $merged) {
            // Double-processing guard: re-check INSIDE the outer transaction,
            // right before the sell() loop, after locking the order row. A plain
            // pre-transaction check is itself racy — two near-simultaneous
            // webhook deliveries could both pass it before either writes.
            $lockedOrder = Order::lockForUpdate()->findOrFail($order->id);
            $hasMovements = \App\Models\StockMovement::where('reference_type', Order::class)
                ->where('reference_id', $lockedOrder->id)
                ->exists();

            if ($hasMovements) {
                return 0; // already stocked out — skip silently
            }

            $count = 0;
            foreach ($merged as $item) {
                $product = Product::findOrFail($item['product_id']);
                $this->stock->sell($product, $item['qty'], $lockedOrder);
                $count++;
            }

            return $count;
        });
    }

    /**
     * Stock-back-in a cancelled/refunded order. Reverses every not-yet-reversed
     * movement that references this order, in one outer transaction, sorted by
     * product_id (deadlock avoidance, same rationale as stockOutOrder).
     */
    public function restoreOrderStock(Order $order): int
    {
        $movements = \App\Models\StockMovement::where('reference_type', Order::class)
            ->where('reference_id', $order->id)
            ->whereNull('reversed_movement_id')
            ->with('product')
            ->orderBy('product_id')
            ->get();

        if ($movements->isEmpty()) {
            return 0;
        }

        return DB::transaction(function () use ($movements) {
            $count = 0;
            foreach ($movements as $movement) {
                if (! $movement->product) continue;
                // reverse() re-locks each product row internally, so nesting is
                // safe here; the outer transaction keeps it all-or-nothing.
                $this->stock->reverse($movement);
                $count++;
            }

            return $count;
        });
    }

    /**
     * Public wrapper around mergeAndSort for callers that only need the merged,
     * product_id-sorted list (e.g. the order store() pre-check pass).
     */
    public function mergeAndSortForValidation($lineItems): array
    {
        return $this->mergeAndSort($lineItems);
    }

    /**
     * Merge line items by product_id (summing qty), then sort by product_id.
     * Accepts either raw request arrays or an OrderItem collection.
     */
    private function mergeAndSort($lineItems): array
    {
        $merged = [];

        foreach ($lineItems as $item) {
            if ($item instanceof OrderItem) {
                $productId = $item->product_id;
                $qty = (int) $item->qty;
            } else {
                $productId = (int) ($item['product_id'] ?? 0);
                $qty = (int) ($item['qty'] ?? 0);
            }

            if ($productId <= 0 || $qty <= 0) continue;

            if (isset($merged[$productId])) {
                $merged[$productId] += $qty;
            } else {
                $merged[$productId] = $qty;
            }
        }

        // Deterministic order across all orders prevents lock-order deadlocks.
        ksort($merged);

        return array_map(
            fn ($productId, $qty) => ['product_id' => $productId, 'qty' => $qty],
            array_keys($merged),
            $merged,
        );
    }
}
