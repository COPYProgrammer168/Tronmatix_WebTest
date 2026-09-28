<?php

// database/migrations/2026_08_06_000008_add_delivery_zone_to_orders_table.php
//
// Persists the delivery zone (phnom_penh | province) computed at checkout by
// the distance-based DeliveryFeeCalculator. Delivery_provider_id already
// exists on orders with an ON DELETE SET NULL FK — left untouched here.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('delivery_zone', ['phnom_penh', 'province'])->nullable()
                ->after('delivery_provider_id')
                ->comment('Zone computed at checkout from the customer location (phnom_penh | province). NULL for pickup or pre-zone orders.');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivery_zone');
        });
    }
};