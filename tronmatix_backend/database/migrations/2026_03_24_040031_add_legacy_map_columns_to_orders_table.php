<?php

// database/migrations/xxxx_xx_xx_add_legacy_map_columns_to_orders_table.php
//
// WHY: The old OrderController wrote delivery_lat/lng/map_address as direct
// columns on the orders table. New code stores these inside the `shipping`
// JSON snapshot instead. This migration adds the columns so old code doesn't
// crash with "column not found", and the blade fallback chain reads them for
// orders placed before the fix.
//
// Run: php artisan migrate

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'delivery_lat')) {
                $table->decimal('delivery_lat', 10, 7)->nullable()->after('delivery_time_slot');
            }
            if (! Schema::hasColumn('orders', 'delivery_lng')) {
                $table->decimal('delivery_lng', 10, 7)->nullable()->after('delivery_lat');
            }
            if (! Schema::hasColumn('orders', 'delivery_map_address')) {
                $table->string('delivery_map_address', 1000)->nullable()->after('delivery_lng');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_lat', 'delivery_lng', 'delivery_map_address']);
        });
    }
};
