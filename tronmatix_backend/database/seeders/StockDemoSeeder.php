<?php

// database/seeders/StockDemoSeeder.php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds realistic stock DEMO data for the inventory system:
 *
 *   1. STOCK-IN   — the first N products that have NO stock history get a
 *                   staged receiving timeline (2–3 bulk deliveries over the
 *                   last ~3 months) via StockService::receiveStock(), which
 *                   creates a signed `in` StockMovement row AND bumps
 *                   current_stock. cost_price is filled on products missing one.
 *
 *   2. STOCK-OUT  — emulates sales: each product then loses stock through
 *                   StockService::sell() (signed `out` movements), stopping
 *                   short of zero so current_stock never goes negative.
 *
 *   3. LOW STOCK  — forces ~1 in 4 products into the low-stock band by setting
 *                   current_stock directly via StockService::adjust() (signed
 *                   'adjustment') to a count <= low_stock_threshold. Those are
 *                   the products the dashboard "low stock" widget and the stock
 *                   page threshold badge flag.
 *
 * SAFETY / IDEMPOTENCY:
 *   - Products that ALREADY have stock history (StockMovement rows) are skipped,
 *     mirroring BackfillStockLedger / ProductSkuSeeder. It is safe to run
 *     repeatedly — nothing is truncated or deleted.
 *   - sell() clamps to available stock so it can never drive a product negative
 *     (StockService throws InsufficientStockException if it would).
 *   - If `products` is empty the seeder reports and stops (you need the catalog
 *     via ProductImportSeeder first).
 *
 * Usage:
 *     php artisan db:seed --class=StockDemoSeeder
 *
 * Khmer guide: see docs/STOCK_DEMO_SEEDER_KM.md
 */
class StockDemoSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(StockService::class);

        // Credit movements to an admin-ish user (created_by). Null is allowed
        // and just means "system" in the history view.
        $actor = User::where('username', '!=', '')->where('role', 'admin')->first()
            ?? User::first();

        $total = Product::count();
        if ($total === 0) {
            $this->command->warn('⚠️  No products found. Run ProductImportSeeder (or import the catalog) first, then run this seeder.');
            return;
        }

        // Only products with no stock history yet — THIS is what makes the
        // seeder idempotent and repeatable.
        $products = Product::whereDoesntHave('stockMovements')
            ->orderBy('id')
            ->limit(60)
            ->get();

        $seeded = 0;
        foreach ($products as $i => $product) {
            $this->seedOne($product, $service, $actor?->id, $i);
            $seeded++;
        }

        $this->command->info("✅ StockDemoSeeder: seeded stock history for {$seeded} product(s).");
        $this->command->info('   Products that already had movements were skipped.');
    }

    /** Play a full in → out → low-stock timeline for one product. */
    private function seedOne(Product $product, StockService $service, ?int $userId, int $index): void
    {
        // ── Fill a cost price if the catalog never set one ──────────────────
        if ($product->cost_price === null || (float) $product->cost_price <= 0) {
            $product->cost_price = round(max(1, (float) $product->price * 0.65), 2);
            $product->saveQuietly();
        }
        $unitCost = (float) $product->cost_price;
        $price    = (float) $product->price;

        // ── Category-aware stock profile ─────────────────────────────────────
        // High-value items (GPUs, laptops) move in small batches; accessories
        // and cables move in larger quantities. Cheap items sell faster.
        $cat = strtolower($product->category ?? '');
        $isHighValue = str_contains($cat, 'gpu') || str_contains($cat, 'laptop') || str_contains($cat, 'monitor');
        $isAccessory = str_contains($cat, 'cable') || str_contains($cat, 'case') || str_contains($cat, 'cooler');
        $isMedium    = str_contains($cat, 'ram') || str_contains($cat, 'ssd') || str_contains($cat, 'hdd');

        if ($isHighValue) {
            $openingBase = 4;
            $sellMax     = 6;
            $damageChance = 15; // out of 100
        } elseif ($isAccessory) {
            $openingBase = 20;
            $sellMax     = 25;
            $damageChance = 5;
        } elseif ($isMedium) {
            $openingBase = 12;
            $sellMax     = 15;
            $damageChance = 8;
        } else {
            $openingBase = max(6, (int) round($price / 80) + 3);
            $sellMax     = 10;
            $damageChance = 10;
        }

        // ── 1. STOCK-IN — staged receiving over the last ~3 months ───────────
        // Vary the total opening quantity per product so inventory looks mixed.
        $opening = $openingBase + rand(-2, 6);
        $opening = max(2, $opening);

        $deliveries = rand(2, 4);
        $remaining  = $opening;

        $deliveryNotes = [
            'PO-2026-' . rand(100, 999) . ' — initial stock-in',
            'PO-2026-' . rand(100, 999) . ' — replenishment',
            'PO-2026-' . rand(100, 999) . ' — express restock',
            'PO-2026-' . rand(100, 999) . ' — monthly order',
        ];

        for ($i = 0; $i < $deliveries; $i++) {
            $qty = ($i === $deliveries - 1)
                ? $remaining
                : (int) floor($opening / $deliveries);
            $qty = max(1, $qty);
            $remaining -= $qty;
            if ($qty <= 0) {
                continue;
            }

            $note = $deliveryNotes[$i] ?? 'PO-' . rand(100, 999) . ' — stock-in';
            $service->receiveStock($product, $qty, $unitCost, 'Demo: ' . $note, $userId);
            $this->backDateLast($product, Carbon::now()->subDays(rand(3, 90)));
        }

        // ── 2) STOCK-OUT — simulated sales over the last ~20 days ───────────
        // Determine how aggressive this product sells: faster for cheap items.
        $sellAggression = $isAccessory ? rand(4, $sellMax) : ($isHighValue ? rand(1, $sellMax) : rand(2, $sellMax));

        $saleNotes = [
            'Online order #' . rand(1000, 9999),
            'Walk-in customer',
            'Online order #' . rand(1000, 9999),
            'Staff demo unit',
            'Online order #' . rand(1000, 9999),
            'Corporate bulk order',
            'Online order #' . rand(1000, 9999),
            'Pre-order fulfilment',
        ];

        for ($i = 0; $i < $sellAggression; $i++) {
            $sold   = $isHighValue ? rand(1, 2) : rand(1, 4);
            $usable = $product->fresh()->current_stock ?? 0;
            $sold   = min($sold, $usable);
            if ($sold <= 0) {
                break;
            }

            $note = $saleNotes[array_rand($saleNotes)];
            $service->sell($product, $sold, null, $userId);
            $this->backDateLast($product, Carbon::now()->subDays(rand(0, 20)));
        }

        // ── 2.5) DAMAGED / WARRANTY RETURN ──────────────────────────────────
        if (rand(1, 100) <= $damageChance) {
            $qtyDamaged = $isHighValue ? 1 : rand(1, 3);
            $usable     = $product->fresh()->current_stock ?? 0;
            if ($usable >= $qtyDamaged) {
                $damageNotes = [
                    'DOA — returned by customer',
                    'Packaging damaged in transit',
                    'Warranty replacement issued',
                    'Display unit scratched',
                ];
                $service->reportDamaged($product, $qtyDamaged, 'Demo: ' . $damageNotes[array_rand($damageNotes)], $userId);
                $this->backDateLast($product, Carbon::now()->subDays(rand(0, 15)));
            }
        }

        // ── 3) STOCK-COUNT ADJUSTMENT — occasional physical count discrepancy ─
        // ~15% of products get a tiny adjustment (±1–2) to mimic real
        // warehouse recounts. This adds realism to the movement history.
        if (rand(1, 100) <= 15) {
            $adjustQty = rand(-2, 2);
            if ($adjustQty !== 0) {
                $fresh = $product->fresh();
                $newTotal = max(0, ($fresh->current_stock ?? 0) + $adjustQty);
                $service->adjust($product, $newTotal, 'Demo: Physical stock count', $userId);
                $this->backDateLast($product, Carbon::now()->subDays(rand(0, 7)));
            }
        }

        // ── 4) LOW-STOCK / SOLD-OUT paint ────────────────────────────────────
        // Every 5th product is pinned low so the dashboard "low stock" widget
        // and the stock-page threshold badge have data to show.
        if ($index % 5 === 0) {
            $threshold = $product->low_stock_threshold ?: 5;
            $service->adjust(
                $product,
                max(0, $threshold - rand(1, 3)),
                'Demo: low-stock marker',
                $userId,
            );
            $this->backDateLast($product, Carbon::now()->subDays(rand(0, 5)));
        }

        // Every 12th product is sold out — shows "Sold Out" badge on storefront.
        if ($index % 12 === 0) {
            $service->adjust($product, 0, 'Demo: out of stock', $userId);
            $this->backDateLast($product, Carbon::now()->subDays(rand(0, 3)));
        }

        // ── Sync stock_status to match final current_stock ───────────────────
        $product->refresh();
        if ($product->current_stock === 0 || $product->current_stock === null) {
            $product->update(['stock_status' => 'Sold Out']);
        } elseif ($product->isLowStock()) {
            $product->update(['stock_status' => 'Low Stock']);
        } else {
            $product->update(['stock_status' => 'Available InStock Now']);
        }

        $final = $product->current_stock ?? 0;
        if ($final <= 0) {
            $this->command->line("   sold-out: {$product->name}");
        } elseif ($product->isLowStock()) {
            $this->command->line("   low-stock: {$product->name} → {$final} units");
        }
    }

    /**
     * Rewind the timestamp of the most recent movement row (and the product
     * pivot affected) so the seeded timeline reads naturally in the history UI.
     */
    private function backDateLast(Product $product, Carbon $stamp): void
    {
        $mv = $product->stockMovements()->latest('id')->first();
        if ($mv) {
            $mv->created_at = $stamp;
            $mv->updated_at = $stamp;
            $mv->saveQuietly();
        }
    }
}