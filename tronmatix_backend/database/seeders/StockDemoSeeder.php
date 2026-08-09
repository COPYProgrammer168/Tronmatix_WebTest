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

        // ── 1. STOCK-IN — staged receiving over the last ~3 months ───────────
        // Split the opening quantity across 2–3 deliveries instead of one lump.
        $opening   = max(8, (int) round((float) $product->price / 50) + 5);
        $deliveries = rand(2, 3);
        $remaining  = $opening;

        for ($i = 0; $i < $deliveries; $i++) {
            $qty = ($i === $deliveries - 1)
                ? $remaining
                : (int) floor($opening / $deliveries);
            $qty = max(1, $qty);
            $remaining -= $qty;
            if ($qty <= 0) {
                continue;
            }

            // receiveStock() adds +qty to current_stock and writes a +qty `in`
            // movement. This is the canonical "stock-in" entry point.
            $service->receiveStock($product, $qty, $unitCost, 'Demo: Stock-in delivery', $userId);

            // Back-date the movement so the history timeline looks realistic.
            $this->backDateLast($product, Carbon::now()->subDays(rand(2, 90)));
        }

        // ── 2) STOCK-OUT — a few days of simulated sales ────────────────────
        $forSale = min((int) floor($opening * 0.35), 12);
        for ($i = 0; $i < $forSale; $i++) {
            $sold   = rand(1, 3);
            $usable = $product->fresh()->current_stock ?? 0;
            $sold   = min($sold, $usable); // clamp so sell() never throws
            if ($sold <= 0) {
                break;
            }

            // sell() writes a -qty `out` movement and decrements current_stock.
            $service->sell($product, $sold, null, $userId);
            $this->backDateLast($product, Carbon::now()->subDays(rand(0, 20)));
        }

        // ── 2.5) DAMAGED/LOST — infrequent loss ─────────────────────────────
        // ~1 in 10 products have some damaged stock.
        if (rand(1, 10) === 1) {
            $qtyDamaged = rand(1, 2);
            $usable     = $product->fresh()->current_stock ?? 0;
            if ($usable >= $qtyDamaged) {
                $service->reportDamaged($product, $qtyDamaged, 'Demo: Damaged item', $userId);
                $this->backDateLast($product, Carbon::now()->subDays(rand(0, 20)));
            }
        }

        // ── 3) LOW STOCK — force ~1/4 of products into the low-stock band ──
        // Every 4th product (index 0 intentionally included) is pinned to just
        // under its per-product threshold via adjust(), so the dashboard "low
        // stock" widget and the stock-page badge flag it.
        if ($index === 0 || $product->id % 4 === 0) {
            $threshold = $product->low_stock_threshold ?: 5;
            $service->adjust(
                $product,
                max(1, $threshold - 2), // e.g. threshold 5 → 3 units left
                'Demo: low stock marker',
                $userId,
            );
            $this->backDateLast($product, Carbon::now()->subDays(rand(0, 5)));
        }

        // Re-read and remind the health of the resulting current_stock.
        $product->refresh();
        $final = $product->low_stock_threshold ?: 5;
        if ($product->current_stock !== null && $product->current_stock <= $final) {
            $this->command->line("   low-stock: {$product->name} → {$product->current_stock} ≤ {$final}");
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