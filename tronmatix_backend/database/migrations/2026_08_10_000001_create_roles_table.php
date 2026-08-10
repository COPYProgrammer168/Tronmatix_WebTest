<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique()
                ->comment('Slug: superadmin, admin, editor, seller, delivery, developer');
            $table->string('label', 100)
                ->comment('Display name');
            $table->string('color', 20)->default('#6b7280')
                ->comment('Hex color for UI badges');
            $table->string('icon', 50)->default('❓')
                ->comment('Emoji icon for UI');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_locked')->default(false)
                ->comment('Cannot be deleted when true (superadmin)');
            $table->boolean('is_staff_portal')->default(true)
                ->comment('Can authenticate via staff/dev portal');
            $table->text('description')->nullable()
                ->comment('Short description shown in the permission matrix');
            $table->json('locked_features')->nullable()
                ->comment('JSON: features locked ON (cannot disable) for this role');
            $table->json('forbidden_features')->nullable()
                ->comment('JSON: features locked OFF (cannot enable) for this role');
            $table->timestamps();
        });

        $now = now();
        DB::table('roles')->insert([
            [
                'key' => 'superadmin',
                'label' => 'Super Admin',
                'color' => '#F97316',
                'icon' => '👑',
                'sort_order' => 1,
                'is_locked' => true,
                'is_staff_portal' => false,
                'locked_features' => json_encode([]),
                'forbidden_features' => json_encode([]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'admin',
                'label' => 'Admin',
                'color' => '#F97316',
                'icon' => '🛡️',
                'sort_order' => 2,
                'is_locked' => false,
                'is_staff_portal' => false,
                'locked_features' => json_encode(['settings', 'staff', 'orders_edit', 'users']),
                'forbidden_features' => json_encode([]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'editor',
                'label' => 'Editor',
                'color' => '#3b82f6',
                'icon' => '✏️',
                'sort_order' => 3,
                'is_locked' => false,
                'is_staff_portal' => true,
                'locked_features' => json_encode([]),
                'forbidden_features' => json_encode(['settings', 'staff', 'users']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'seller',
                'label' => 'Seller',
                'color' => '#10b981',
                'icon' => '🏪',
                'sort_order' => 4,
                'is_locked' => false,
                'is_staff_portal' => true,
                'locked_features' => json_encode([]),
                'forbidden_features' => json_encode(['settings', 'staff', 'users']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'delivery',
                'label' => 'Delivery',
                'color' => '#a855f7',
                'icon' => '🚚',
                'sort_order' => 5,
                'is_locked' => false,
                'is_staff_portal' => true,
                'locked_features' => json_encode([]),
                'forbidden_features' => json_encode(['settings', 'staff', 'users']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'developer',
                'label' => 'Developer',
                'color' => '#06b6d4',
                'icon' => '💻',
                'sort_order' => 6,
                'is_locked' => false,
                'is_staff_portal' => false,
                'locked_features' => json_encode([]),
                'forbidden_features' => json_encode(['settings', 'staff', 'users']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
