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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            // in | out | adjustment | damaged | reversal
            $table->string('type', 20);

            // Always signed to match its effect on stock:
            //  + : stock increases (in, adjustment-up, reversal of an out)
            //  - : stock decreases (out, damaged, adjustment-down, reversal of an in)
            $table->integer('quantity');

            // NEVER signed — always a positive cost-per-unit basis, regardless of
            // whether the movement increases or decreases stock. A reversal row
            // copies the original movement's unit_cost unchanged.
            $table->decimal('unit_cost', 10, 2)->nullable();

            $table->text('note')->nullable();

            // Polymorphic reference (order, etc.) — creates the composite index.
            $table->nullableMorphs('reference');

            // Self-referencing FK: set on a reversal row to point at the movement
            // it reversed. Unique (nullable) → DB-level guarantee a movement can
            // never be reversed twice.
            $table->unsignedBigInteger('reversed_movement_id')->nullable();
            $table->foreign('reversed_movement_id')->references('id')->on('stock_movements')->nullOnDelete();
            $table->unique('reversed_movement_id');

            // Never cascade-delete ledger rows just because a user account goes.
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // History page always queries by product, ordered by date.
            $table->index(['product_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
