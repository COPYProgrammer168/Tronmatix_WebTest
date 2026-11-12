<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Auto-generated SKU: {PREFIX}{5 random chars}, e.g. CPUA7BQP.
            // Nullable so backfill can assign in batches; unique index is the
            // database-level backstop against duplicate SKUs.
            $table->string('sku', 30)->nullable()->after('brand');
            $table->unique('sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->dropColumn('sku');
        });
    }
};
