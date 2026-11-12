<?php

// database/migrations/2026_08_06_000004_add_customer_delivery_note_to_orders_table.php
//
// Lets the customer write an optional note (or confirm with a message) when
// they confirm delivery from the Telegram bot. Also tracks WHO confirmed
// delivery (customer vs staff/admin) and when.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('customer_delivery_note')->nullable()
                ->after('delivery_confirmed_at')
                ->comment('Optional message from the customer when they confirm delivery in Telegram');
            $table->string('delivery_confirmed_by', 30)->nullable()
                ->after('customer_delivery_note')
                ->comment('who confirmed: customer | staff | admin');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['customer_delivery_note', 'delivery_confirmed_by']);
        });
    }
};