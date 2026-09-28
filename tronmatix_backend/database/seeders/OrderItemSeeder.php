<?php

// database/seeders/OrderItemSeeder.php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Creates order_items for each order.
 *
 * OrderSeeder builds the carts (1–3 products each) and calls
 * seedForOrder() here so the item rows share the exact same cart math that
 * computed the order subtotal — order totals and item lines never drift.
 *
 * Each item snapshots the product name/price/image and derives a warranty
 * window from the product's warranty string (used by the order views).
 */
class OrderItemSeeder extends Seeder
{
    /**
     * @param  Order  $order         the order these lines belong to
     * @param  array  $productLines  [['product' => Product, 'qty' => int], ...]
     * @param  Carbon  $at           order timestamp (items share it)
     */
    public function seedForOrder(Order $order, array $productLines, Carbon $at): void
    {
        foreach ($productLines as $line) {
            $product = $line['product'];
            $price   = $this->cleanPrice($product->price);

            $warranty = $this->parseWarranty($product->warranty);

            OrderItem::create([
                'order_id'        => $order->id,
                'product_id'      => $product->id,
                'name'            => $product->name,
                'price'           => $price,
                'qty'             => $line['qty'],
                'image'           => $product->image ?? null,
                'brand'           => $product->brand,
                'warranty_start'  => $warranty['start'],
                'warranty_end'    => $warranty['end'],
                'created_at'      => $at,
                'updated_at'      => $at,
            ]);
        }
    }

    /**
     * Standalone seam: when invoked via `db:seed --class=OrderItemSeeder`
     * there is nothing to do — items are always generated as part of
     * OrderSeeder via seedForOrder(). Kept so `php artisan db:seed` doesn't
     * error if another seeder references the class.
     */
    public function run(): void
    {
        $this->command->info('✅ OrderItemSeeder: (no-op — items are created by OrderSeeder).');
    }

    private function cleanPrice($raw): float
    {
        $clean = preg_replace('/[^0-9.]/', '', (string) $raw);

        return $clean === '' ? 0.0 : (float) $clean;
    }

    /** Convert "1 year"/"2 years"/"3 years" to start/end date window. */
    private function parseWarranty(?string $warranty): array
    {
        if (! $warranty || preg_match('/(\d+)\s*years?/i', $warranty, $m) !== 1) {
            return ['start' => null, 'end' => null];
        }

        $start = Carbon::now();

        return [
            'start' => $start,
            'end'   => $start->copy()->addYears((int) $m[1]),
        ];
    }
}