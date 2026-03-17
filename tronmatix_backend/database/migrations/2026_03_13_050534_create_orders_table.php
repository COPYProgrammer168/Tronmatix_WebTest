<?php

// database/migrations/2026_03_01_000005_create_orders_table.php
//
// TABLES: orders + order_items
// ── Fix vs previous version ───────────────────────────────────────────────────
//   + orders.location_id  — FK → user_locations (nullable, nullOnDelete)
//                           Checkout page links a saved address to the order.
//                           This is separate from the shipping JSON snapshot:
//                           snapshot = what was used; location_id = which saved
//                           address was selected (may be updated by user later).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── orders ────────────────────────────────────────────────────────────
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // ── Identity ──────────────────────────────────────────────────────
            $table->string('order_id', 30)->unique()
                ->comment('Human-readable reference: TRX-XXXXXXXX');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // ── Payment ───────────────────────────────────────────────────────
            $table->string('payment_method', 30)
                ->comment('bakong | cash | card');
            $table->string('payment_status', 30)->default('pending')
                ->comment('pending | paid | failed | manual_pending | refunded');
            $table->string('payment_ref', 100)->nullable()
                ->comment('ABA APV approval code or Bakong hash on confirmation');

            // ── Order status ──────────────────────────────────────────────────
            $table->string('status', 30)->default('pending')
                ->comment('pending | confirmed | processing | shipped | delivered | cancelled');

            // ── Financials ────────────────────────────────────────────────────
            $table->decimal('subtotal', 10, 2)->default(0)
                ->comment('Cart total before discount/delivery/tax');
            $table->foreignId('discount_id')->nullable()
                ->constrained('discounts')->nullOnDelete()
                ->comment('FK → discounts — NULL if no coupon used');
            $table->string('discount_code', 50)->nullable()
                ->comment('Snapshot of coupon code (discount record may be deleted)');
            $table->decimal('discount_amount', 10, 2)->default(0)
                ->comment('Dollar amount deducted from subtotal');
            $table->decimal('delivery', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0)
                ->comment('Final: subtotal − discount + delivery + tax');

            // ── Shipping ──────────────────────────────────────────────────────
            // location_id: FK to the saved address that was selected at checkout.
            // shipping JSON: immutable snapshot of address at time of order.
            // Both exist simultaneously — location_id for linking, JSON for history.
            $table->unsignedBigInteger('location_id')->nullable()
                ->comment('FK → user_locations — which saved address was used');
            $table->foreign('location_id')
                ->references('id')->on('user_locations')->nullOnDelete();

            $table->json('shipping')->nullable()
                ->comment('Snapshot at order time: {name, phone, address, city, country, note}');

            // ── Delivery scheduling ───────────────────────────────────────────
            $table->date('delivery_date')->nullable();
            $table->string('delivery_time_slot', 50)->nullable()
                ->comment('e.g. "Morning (8AM–12PM)"');
            $table->timestamp('delivery_confirmed_at')->nullable()
                ->comment('Set when admin marks order as delivered');

            $table->timestamps();

            // ── Indexes ───────────────────────────────────────────────────────
            $table->index('status');
            $table->index('payment_status');
            $table->index('user_id');
            $table->index('created_at');
            $table->index('delivery_date');
            $table->index('location_id');
        });

        // ── order_items ───────────────────────────────────────────────────────
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete()
                ->comment('NULL if product deleted after order was placed');

            // ── Snapshots (locked at order time) ──────────────────────────────
            $table->string('name')
                ->comment('Product name at time of order');
            $table->decimal('price', 10, 2)
                ->comment('Unit price at time of order');
            $table->unsignedInteger('qty');
            $table->string('image')->nullable()
                ->comment('Product image at time of order');

            $table->timestamps();

            $table->index('order_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
