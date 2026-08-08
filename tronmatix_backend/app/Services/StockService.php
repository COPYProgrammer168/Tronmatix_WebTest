<?php

// app/Services/StockService.php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Single entry point for every stock change. All mutations go through the
 * lock + transaction pattern below so two concurrent operations can never both
 * succeed on the last unit of stock.
 *
 * Lock order (the part to get right): acquire the product row lock FIRST,
 * then read the fresh current_stock under the lock, THEN validate, THEN write.
 * Validating against the un-locked $product argument passed in (which may be
 * stale) before acquiring the lock recreates the exact race this is meant to
 * prevent.
 */
class StockService
{
    /**
     * Add stock to a product.
     *
     * @param  int  $quantity  positive units being received
     * @param  float  $unitCost  positive cost-per-unit, stored as-is
     */
    public function receiveStock(
        Product $product,
        int $quantity,
        float $unitCost,
        ?string $note = null,
        ?int $userId = null,
    ): StockMovement {
        $quantity = max(1, $quantity);
        $unitCost = max(0, $unitCost);

        return $this->withLockedProduct($product, function (Product $locked) use ($quantity, $unitCost, $note, $userId) {
            $locked->increment('current_stock', $quantity);

            return $this->record($locked, StockMovement::TYPE_IN, $quantity, $unitCost, $note, null, null, $userId);
        });
    }

    /**
     * Remove stock for a sale/fulfillment. Throws if quantity exceeds current
     * stock. unit_cost is not meaningful for a sale (that's a selling-price
     * concern, not a cost concern) — left null on 'out' movements.
     */
    public function sell(
        Product $product,
        int $quantity,
        ?Model $reference = null,
        ?int $userId = null,
    ): StockMovement {
        $quantity = max(1, $quantity);

        return $this->withLockedProduct($product, function (Product $locked) use ($quantity, $reference, $userId) {
            $this->assertSufficient($locked, $quantity);
            $locked->decrement('current_stock', $quantity);

            return $this->record(
                $locked,
                StockMovement::TYPE_OUT,
                -$quantity,
                null,
                null,
                $reference ? $reference::class : null,
                $reference ? $reference->getKey() : null,
                $userId,
            );
        });
    }

    /**
     * Set stock to a counted total. Logs the difference as an 'adjustment'
     * movement. Returns null when counted == current (no-op, no row created).
     */
    public function adjust(
        Product $product,
        int $countedQuantity,
        ?string $note = null,
        ?int $userId = null,
    ): ?StockMovement {
        $countedQuantity = max(0, $countedQuantity);

        return $this->withLockedProduct($product, function (Product $locked) use ($countedQuantity, $note, $userId) {
            $current = $locked->current_stock ?? 0;
            $diff = $countedQuantity - $current;

            if ($diff === 0) {
                return null; // no-op — don't clutter history
            }

            $locked->update(['current_stock' => $countedQuantity]);

            // unit_cost is null on adjustments — a stock count doesn't imply a
            // cost basis for the discrepancy, and inventing one would corrupt
            // the dashboard's total-inventory-value calculation.
            return $this->record($locked, StockMovement::TYPE_ADJUSTMENT, $diff, null, $note, null, null, $userId);
        });
    }

    /**
     * Log damaged/lost stock. Negative-quantity 'damaged' movement.
     * unit_cost stays null for the same reason as adjust().
     */
    public function reportDamaged(
        Product $product,
        int $quantity,
        string $note,
        ?int $userId = null,
    ): StockMovement {
        $quantity = max(1, $quantity);

        return $this->withLockedProduct($product, function (Product $locked) use ($quantity, $note, $userId) {
            $this->assertSufficient($locked, $quantity);
            $locked->decrement('current_stock', $quantity);

            return $this->record($locked, StockMovement::TYPE_DAMAGED, -$quantity, null, $note, null, null, $userId);
        });
    }

    /**
     * Undo a movement (order cancellation/refund). Creates an opposite-signed
     * 'reversal' row pointing back via reversed_movement_id. The double-reversal
     * check runs AFTER acquiring the product lock — see docblock above.
     */
    public function reverse(
        StockMovement $movement,
        ?int $userId = null,
    ): StockMovement {
        return $this->withLockedProduct($movement->product, function (Product $locked) use ($movement, $userId) {
            // Locked now — only check the double-reversal after the lock, since a
            // pre-lock check would let two concurrent reverses both pass.
            $alreadyReversed = StockMovement::where('reversed_movement_id', $movement->id)->exists();
            if ($alreadyReversed) {
                throw new \RuntimeException('This movement has already been reversed.');
            }

            // Reversing flips the quantity effect: a +qty receive becomes -qty,
            // a -qty sale becomes +qty. Reversing an 'in' (receive) can therefore
            // fail the same way a sell() can — guard against going negative.
            $reversalQty = -$movement->quantity;
            if ($reversalQty < 0) {
                $this->assertSufficient($locked, abs($reversalQty));
            }

            $locked->update(['current_stock' => ($locked->current_stock ?? 0) + $reversalQty]);

            // unit_cost copied unchanged from the movement being reversed — don't
            // flip its sign and don't recompute it.
            return $this->record(
                $locked,
                StockMovement::TYPE_REVERSAL,
                $reversalQty,
                $movement->unit_cost,
                'Reversal of ' . $movement->typeLabel() . ' #' . $movement->id . ($movement->note ? ': ' . $movement->note : ''),
                $movement->reference_type,
                $movement->reference_id,
                $userId,
                $movement->id,
            );
        });
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Run $callback inside a transaction with the product row locked. Re-fetches
     * the product under the lock (Product::lockForUpdate()->find(id)) rather than
     * trusting the passed instance, which may be stale.
     */
    private function withLockedProduct(Product $product, callable $callback)
    {
        return DB::transaction(function () use ($product, $callback) {
            // Lock the product row, then re-read it fresh under the lock. The
            // order matters: lock first, THEN read, THEN validate, THEN write.
            $locked = Product::lockForUpdate()->findOrFail($product->id);

            return $callback($locked);
        });
    }

    private function assertSufficient(Product $product, int $qty): void
    {
        $available = $product->current_stock ?? 0;
        if ($available < $qty) {
            throw new InsufficientStockException($product, $qty, $available);
        }
    }

    /**
     * Insert a movement row (already inside the caller's transaction).
     */
    private function record(
        Product $product,
        string $type,
        int $quantity,
        $unitCost,
        ?string $note,
        ?string $refType,
        $refId,
        ?int $userId,
        ?int $reversedMovementId = null,
    ): StockMovement {
        return StockMovement::create([
            'product_id'           => $product->id,
            'type'                 => $type,
            'quantity'             => $quantity,
            'unit_cost'            => $unitCost,
            'note'                 => $note,
            'reference_type'       => $refType,
            'reference_id'         => $refId,
            'reversed_movement_id' => $reversedMovementId,
            'created_by'           => $userId,
        ]);
    }
}
