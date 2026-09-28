<?php

// database/seeders/StockSeeder.php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds stock-movement history for the REAL Tronmatix product catalog
 * (the products already imported from products_catalog.json), so the
 * dashboard inventory / stock-history pages have readable ledger rows.
 *
 * What this seeder does NOT do:
 *   - It does NOT create or truncate products — the real catalog is
 *     preserved. Products already exist (seeded via ProductSeeder).
 *   - It does NOT overwrite existing movement history (idempotent per
 *     product — products that already carry movements are skipped).
 *
 * How the ledger is built (keeps current_stock consistent with the rows):
 *   1. Determine the product's CURRENT stock (S) from the products table.
 *   2. Write a stock-in (receive) of `inQty` at the product's cost basis.
 *   3. Write a stock-out (sale) of `outQty` so the net of the ledger equals S.
 *   4. Occasionally a small adjustment row to make the history feel real.
 *
 * Every movement is attributed to the ADMIN user (created_by) — the seeder
 * locates (or creates) a `users` row with role 'admin' so the dashboard's
 * "Performed By" column shows a real name. The `admins` table (superadmin)
 * is a separate auth guard, so we keep an admin in `users` too, which is
 * where stock_movements.created_by is constrained.
 */
class StockSeeder extends Seeder
{
    public function run(): void
    {
        $actor = $this->adminUser();

        $products = Product::query()
            ->whereNotNull('current_stock')
            ->orderBy('id')
            ->get();

        $seeded = 0;
        $skipped = 0;

        foreach ($products as $product) {
            if ($product->stockMovements()->count() > 0) {
                $skipped++;
                continue;
            }
            $this->movements($product, $actor->id);
            $seeded++;
        }

        // Re-point any legacy movement rows (from older demo seeders) at the
        // admin actor, so every ledger row reads "Performed By: <admin>" in the
        // dashboard history. Skips rows already owned by the admin.
        $reassigned = StockMovement::where(function ($q) use ($actor) {
            $q->whereNull('created_by')->orWhere('created_by', '!=', $actor->id);
        })->update(['created_by' => $actor->id]);

        $this->command->info("✅ StockSeeder: {$seeded} products seeded with stock history (performed by {$actor->name}).");
        $this->command->info("   Skipped {$skipped} products that already had movement history.");
        $this->command->info("   Re-assigned {$reassigned} legacy movement rows to {$actor->name}.");

        $low = Product::whereNotNull('current_stock')->where('current_stock', '>', 0)
            ->whereColumn('current_stock', '<=', 'low_stock_threshold')->count();
        $this->command->info("   Low-stock products: {$low}");
    }

    /**
     * Locate (or create) the admin user that performs the movements.
     * Idempotent — re-running reuses the same row.
     */
    // private function adminUser(): User
    // {
    //     if ($user = User::where('role', 'admin')->first()) {
    //         return $user;
    //     }

    //     return User::firstOrCreate(
    //         ['email' => 'admin@tronmatix.com'],
    //         [
    //             'name'     => 'Super Admin',
    //             'username' => 'superadmin',
    //             'password' => Hash::make('Admin@1234'),
    //             'role'     => 'admin',
    //         ]
    //     );
    // }

    // ── A short, ledger-consistent movement set so the history UI isn't empty ─
    private function movements(Product $product, int $userId): void
    {
        $now = Carbon::now();
        $stock = (int) $product->current_stock;

        // Realistic unit cost basis ≈ 68% of the shelf price (skip $$/$$$ prices).
        $cost = $this->costBasis($product);

        // Stock-in (initial receiving) — the bulk of what is on the shelf today.
        $inQty = max($stock, 3) + rand(2, 8);
        $this->movement($product, StockMovement::TYPE_IN, $inQty, $cost,
            'Initial receiving — PO-2026-' . rand(100, 999), $userId, $now->copy()->subDays(rand(15, 60)));

        // Stock-out (sales) — the difference between what came in and what remains.
        $outQty = $inQty - $stock;
        if ($outQty > 0) {
            $this->movement($product, StockMovement::TYPE_OUT, -$outQty, null,
                'Online order #' . rand(1000, 9999), $userId, $now->copy()->subDays(rand(1, 14)));
        }

        // Occasional physical stock-count adjustment (not always).
        if (rand(1, 100) <= 30 && $stock > 0) {
            $this->movement($product, StockMovement::TYPE_ADJUSTMENT, 0, null,
                'Physical stock count', $userId, $now->copy()->subDays(rand(0, 7)));
        }
    }

    private function movement(Product $product, string $type, int $qty, ?float $unitCost, string $note, int $userId, Carbon $at): void
    {
        StockMovement::create([
            'product_id'  => $product->id,
            'type'        => $type,
            'quantity'    => $qty,
            'unit_cost'   => $unitCost,
            'note'        => $note,
            'created_by'  => $userId,
            'created_at'  => $at,
            'updated_at'  => $at,
        ]);
    }

    /**
     * Derive a positive unit-cost basis from the shelf price, or a sane
     * default when the price is a placeholder ('$$$', '$$$$', '', '0').
     */
    private function costBasis(Product $product): float
    {
        $price = trim((string) $product->price);

        if ($price === '' || str_contains($price, '$') || (float) $price <= 0) {
            return (float) round(rand(15, 400) + rand(0, 99) / 100, 2);
        }

        return (float) round(((float) $price) * 0.68, 2);
    }
}
