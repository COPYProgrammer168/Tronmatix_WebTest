<?php

// database/migrations/2026_08_06_000005_add_delivery_confirm_requested_at_to_orders_table.php
//
// Tracks the moment staff/courier marks the "delivery run" complete (shipped →
// delivered in the dashboard). The order does NOT immediately become delivered;
// instead we stamp this timestamp and send the customer a "Confirm Received"
// button in Telegram. The order becomes delivered when the customer taps it
// (delivery_confirmed_by = 'customer'), or staff forces it via the fallback.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('delivery_confirm_requested_at')->nullable()
                ->after('delivery_confirmed_at')
                ->comment('Set when staff/courier marks the delivery run complete and customer confirmation is requested');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivery_confirm_requested_at');
        });
    }
};