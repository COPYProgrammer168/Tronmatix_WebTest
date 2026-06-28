<?php

// database/migrations/xxxx_xx_xx_add_fulfillment_type_to_orders_table.php
// Run: php artisan migrate

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // 'delivery' = standard delivery to address (default)
            // 'pickup'   = customer picks up at store themselves
            $table->enum('fulfillment_type', ['delivery', 'pickup'])
                  ->default('delivery')
                  ->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('fulfillment_type');
        });
    }
};
