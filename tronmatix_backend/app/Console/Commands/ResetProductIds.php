<?php

// app/Console/Commands/ResetProductIds.php

namespace App\Console\Commands;

use App\Models\Banner;
use App\Models\Discount;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetProductIds extends Command
{
    protected $signature = 'db:reset-product-ids';
    protected $description = 'Reset product IDs to sequential 1,2,3… and update all FK references';

    // PostgreSQL table + constraint referencing products
    private array $fkConstraints = [
        ['table' => 'order_items', 'constraint' => 'order_items_product_id_foreign'],
        ['table' => 'discounts',   'constraint' => 'discounts_product_id_foreign'],
        ['table' => 'banners',     'constraint' => 'banners_product_id_foreign'],
        ['table' => 'videos',      'constraint' => 'videos_product_id_foreign'],
    ];

    public function handle(): int
    {
        $products = Product::orderBy('id')->get();

        if ($products->isEmpty()) {
            $this->warn('⚠️  No products found.');
            return Command::SUCCESS;
        }

        if ($products->first()->id === 1) {
            $this->info('✅ Product IDs are already sequential from 1. Nothing to do.');
            return Command::SUCCESS;
        }

        $count = $products->count();
        $oldMin = $products->first()->id;
        $oldMax = $products->last()->id;

        $this->warn("Reassigning {$count} product IDs ({$oldMin}–{$oldMax} → 1–{$count})...");

        // Build mapping: old ID → new sequential ID
        $newId = 0;
        $map = [];
        foreach ($products as $product) {
            $newId++;
            $map[$product->id] = $newId;
        }

        $bar = $this->output->createProgressBar(8);
        $bar->start();

        DB::beginTransaction();

        try {
            // 1. Drop FK constraints
            foreach ($this->fkConstraints as $fk) {
                DB::statement("ALTER TABLE \"{$fk['table']}\" DROP CONSTRAINT IF EXISTS \"{$fk['constraint']}\"");
            }
            $bar->advance();

            // 2. Update order_items FK
            foreach ($map as $oldId => $newIdVal) {
                OrderItem::where('product_id', $oldId)->update(['product_id' => $newIdVal]);
            }
            $bar->advance();

            // 3. Update discounts FK
            foreach ($map as $oldId => $newIdVal) {
                Discount::where('product_id', $oldId)->update(['product_id' => $newIdVal]);
            }
            $bar->advance();

            // 4. Update banners FK
            foreach ($map as $oldId => $newIdVal) {
                Banner::where('product_id', $oldId)->update(['product_id' => $newIdVal]);
            }
            $bar->advance();

            // 5. Update videos FK
            foreach ($map as $oldId => $newIdVal) {
                Video::where('product_id', $oldId)->update(['product_id' => $newIdVal]);
            }
            $bar->advance();

            // 6. Reassign product IDs in descending order to avoid PK conflict
            $products->sortByDesc('id')->each(function ($product) use ($map) {
                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['id' => $map[$product->id]]);
            });
            $bar->advance();

            // 7. Reset the sequence
            $maxId = Product::max('id');
            DB::statement("ALTER SEQUENCE products_id_seq RESTART WITH " . ($maxId + 1));
            $bar->advance();

            // 8. Restore FK constraints
            foreach ($this->fkConstraints as $fk) {
                $onDelete = match ($fk['table']) {
                    'discounts' => 'CASCADE',
                    default     => 'SET NULL',
                };
                DB::statement("
                    ALTER TABLE \"{$fk['table']}\"
                    ADD CONSTRAINT \"{$fk['constraint']}\"
                    FOREIGN KEY (product_id)
                    REFERENCES products(id)
                    ON DELETE {$onDelete}
                ");
            }
            $bar->advance();

            DB::commit();

            $bar->finish();
            $this->newLine();

            $this->info("✅ {$count} product IDs reset ({$oldMin}–{$oldMax} → 1–{$count}). Sequence updated to " . ($maxId + 1) . '.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('❌ Failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
