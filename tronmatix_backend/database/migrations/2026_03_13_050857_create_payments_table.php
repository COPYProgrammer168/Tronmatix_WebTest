<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // ── Identity ──────────────────────────────────────────────────────
            $table->unsignedBigInteger('order_id')->nullable()
                ->comment('FK → orders.id — nullable so payment survives order deletion');
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();

            $table->string('tran_id', 100)->nullable()->unique()
                ->comment('Internal transaction ref e.g. TRX-XXXXXXXX or ABA tran_id');
            $table->string('provider', 30)->default('bakong')
                ->comment('Payment gateway: bakong | cash | stripe | aba');
            $table->string('payment_method', 30)->default('bakong')
                ->comment('User-facing label: bakong | cash | card');

            // ── Amount ────────────────────────────────────────────────────────
            $table->decimal('amount', 12, 2)->default(0)
                ->comment('Charged amount — 2 decimal places');
            $table->string('currency', 10)->default('USD')
                ->comment('ISO 4217: USD | KHR');

            // ── QR / KHQR ─────────────────────────────────────────────────────
            $table->text('qr_data')->nullable()
                ->comment('Full KHQR string from BakongKHQR::generateIndividual()');
            $table->string('qr_md5', 64)->nullable()
                ->comment('MD5 of qr_data — used in NBC check_transaction_by_md5 poll');
            $table->unsignedBigInteger('qr_expiration')->nullable()
                ->comment('Legacy: Date.now() ms timestamp — kept for Node.js backwards compat');
            $table->timestamp('qr_expires_at')->nullable()
                ->comment('Proper datetime expiry — used by PHP Carbon comparisons');

            // ── Bakong API response ────────────────────────────────────────────
            $table->string('apv', 100)->nullable()
                ->comment('ABA approval code from webhook');
            $table->string('bakong_hash', 255)->nullable()
                ->comment('data.hash from NBC check_transaction_by_md5 response');
            $table->string('from_account_id', 100)->nullable()
                ->comment('data.fromAccountId — payer Bakong account');
            $table->string('to_account_id', 100)->nullable()
                ->comment('data.toAccountId — merchant Bakong account');
            $table->text('description')->nullable()
                ->comment('data.description from Bakong response');
            $table->string('transaction_id', 255)->nullable()
                ->comment('Final confirmed transaction ID — equals bakong_hash on success');

            // ── Status flags ──────────────────────────────────────────────────
            $table->string('status', 30)->default('pending')
                ->comment('pending | paid | expired | failed | manual_pending | refunded');
            $table->boolean('paid')->default(false)
                ->comment('Boolean mirror — true when status=paid');
            $table->timestamp('paid_at')->nullable()
                ->comment('When payment was confirmed by NBC or manually');
            $table->timestamp('expires_at')->nullable()
                ->comment('Overall session expiry (≥ qr_expires_at)');

            // ── Extra ─────────────────────────────────────────────────────────
            $table->json('meta')->nullable()
                ->comment('Arbitrary payload: raw Bakong response, webhook body, debug');

            $table->timestamps();

            // ── Indexes ───────────────────────────────────────────────────────
            $table->unique('qr_md5');                          // fast poll lookup
            $table->index('order_id');                         // join with orders
            $table->index('status');                           // dashboard filters
            $table->index('paid_at');                          // date-range queries
            $table->index('qr_expires_at');                    // expiry cleanup
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
