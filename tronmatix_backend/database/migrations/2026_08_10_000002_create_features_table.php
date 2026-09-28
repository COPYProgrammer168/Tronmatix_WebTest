<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique()
                ->comment('Slug: dashboard, products, orders, orders_edit, users, discounts, report, settings, staff, stock, activity_log');
            $table->string('label', 100)
                ->comment('Display name');
            $table->string('icon', 50)->default('📄')
                ->comment('Emoji icon for UI');
            $table->string('category', 50)->nullable()
                ->comment('Optional grouping: general, admin, inventory, customer');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        DB::table('features')->insert([
            ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => '📊', 'category' => 'general', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'products', 'label' => 'Products & Banners', 'icon' => '📦', 'category' => 'inventory', 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'orders', 'label' => 'Orders (view)', 'icon' => '📋', 'category' => 'customer', 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'orders_edit', 'label' => 'Orders (edit)', 'icon' => '✏️', 'category' => 'customer', 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'users', 'label' => 'Users & Customers', 'icon' => '👥', 'category' => 'admin', 'sort_order' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'discounts', 'label' => 'Discounts', 'icon' => '🏷️', 'category' => 'customer', 'sort_order' => 6, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'report', 'label' => 'Reports', 'icon' => '📈', 'category' => 'admin', 'sort_order' => 7, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'settings', 'label' => 'Settings', 'icon' => '⚙️', 'category' => 'admin', 'sort_order' => 8, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'staff', 'label' => 'Staff & Roles', 'icon' => '🛡️', 'category' => 'admin', 'sort_order' => 9, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'stock', 'label' => 'Stock Management', 'icon' => '📊', 'category' => 'inventory', 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'activity_log', 'label' => 'Activity Log', 'icon' => '📝', 'category' => 'admin', 'sort_order' => 11, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
