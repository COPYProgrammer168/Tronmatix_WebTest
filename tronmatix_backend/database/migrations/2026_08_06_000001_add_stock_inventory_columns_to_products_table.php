<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ── Rename the existing `stock` column to `current_stock` ─────────────
        // The legacy `stock` column was already the de-facto current stock
        // (non-null, non-negative for every product). Renaming makes it the
        // explicit system-of-record column the inventory ledger writes to.
        // The Product model keeps a `stock` accessor so existing code and the
        // storefront API still read `$product->stock`.
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('stock', 'current_stock');

            if (! Schema::hasColumn('products', 'cost_price')) {
                $table->decimal('cost_price', 10, 2)->nullable()->after('current_stock');
            }
            if (! Schema::hasColumn('products', 'low_stock_threshold')) {
                $table->integer('low_stock_threshold')->default(5)->after('cost_price');
            }
        });

        // Database-level safety net: current stock can never go negative, even
        // if a bug slips past the application layer. Postgres-specific syntax —
        // guard by driver so the SQLite :memory: test DB can also migrate.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE products ADD CONSTRAINT stock_non_negative CHECK (current_stock >= 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE products DROP CONSTRAINT IF EXISTS stock_non_negative');
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['cost_price', 'low_stock_threshold']);
            $table->renameColumn('current_stock', 'stock');
        });
    }
};
