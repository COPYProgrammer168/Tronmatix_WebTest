<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique()
                ->comment('Setting identifier — snake_case');
            $table->text('value')->nullable()
                ->comment('Stored as string; cast to int/bool in AdminSetting::get()');
            $table->timestamps();
        });

        // ── Default seeds ──────────────────────────────────────────────────────
        $now = now();
        $defaults = [
            // Notifications
            ['key' => 'notif_low_stock',           'value' => '1',                    'comment' => 'Show low-stock bell alert'],
            ['key' => 'notif_low_stock_threshold',  'value' => '5',                    'comment' => 'Stock ≤ N triggers alert'],
            ['key' => 'notif_new_order',            'value' => '1',                    'comment' => 'Alert on new order (today)'],
            ['key' => 'notif_pending_payment',      'value' => '1',                    'comment' => 'Alert on awaiting KHQR payment'],
            ['key' => 'notif_qr_confirmed',         'value' => '1',                    'comment' => 'Alert when ABA BAKONG auto-confirms'],
            ['key' => 'notif_delivery_confirm',     'value' => '1',                    'comment' => 'Alert when delivery confirmed today'],
            // Order automation
            ['key' => 'order_auto_confirm_cash',    'value' => '0',                    'comment' => '1 = auto-move cash orders pending→confirmed'],
            ['key' => 'order_auto_cancel_hours',    'value' => '0',                    'comment' => 'Cancel pending orders after N hours (0=off)'],
            // Store
            ['key' => 'store_name',                 'value' => 'Tronmatix Computer',   'comment' => 'Displayed in receipts and emails'],
            ['key' => 'store_currency',             'value' => 'USD',                  'comment' => 'ISO 4217 default currency'],
            ['key' => 'store_open',                 'value' => '1',                    'comment' => '0 = show closed notice on storefront'],
            // Display
            ['key' => 'dashboard_rows_per_page',    'value' => '20',                   'comment' => 'Orders/payments table page size'],
            ['key' => 'products_per_page',          'value' => '12',                   'comment' => 'Storefront product grid page size'],
        ];

        foreach ($defaults as $row) {
            DB::table('admin_settings')->insertOrIgnore([
                'key' => $row['key'],
                'value' => $row['value'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_settings');
    }
};
