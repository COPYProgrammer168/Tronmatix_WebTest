<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();

            // ── Identity ──────────────────────────────────────────────────────
            $table->string('code', 50)->unique()
                ->comment('Coupon code entered by customer, e.g. "SAVE20"');

            // ── Type & value ──────────────────────────────────────────────────
            $table->enum('type', ['percentage', 'fixed'])->default('percentage')
                ->comment('"percentage" = deduct X% of subtotal, "fixed" = deduct $X');
            $table->decimal('value', 10, 2)
                ->comment('Discount amount: 20 means 20% or $20 depending on type');

            // ── Conditions ────────────────────────────────────────────────────
            $table->decimal('min_order', 10, 2)->default(0)
                ->comment('Cart subtotal must be ≥ this to apply the coupon');
            $table->unsignedInteger('max_uses')->nullable()
                ->comment('NULL = unlimited uses');
            $table->unsignedInteger('used_count')->default(0)
                ->comment('Incremented every time the coupon is successfully used');
            $table->timestamp('expires_at')->nullable()
                ->comment('NULL = never expires');

            // ── State ─────────────────────────────────────────────────────────
            $table->boolean('is_active')->default(true)
                ->comment('false = coupon disabled (not deleted)');

            // ── Targeting ─────────────────────────────────────────────────────
            $table->json('categories')->nullable()
                ->comment('JSON array of category slugs; NULL = applies to all');

            $table->json('badge_config')->nullable()->after('categories');

            $table->timestamps();

            // ── Indexes ───────────────────────────────────────────────────────
            $table->index('code');
            $table->index('is_active');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
