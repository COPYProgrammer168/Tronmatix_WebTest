<?php

// database/migrations/xxxx_add_telegram_fields_to_users_table.php
// Run: php artisan make:migration add_telegram_fields_to_users_table
// Then replace content with this file.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'telegram_chat_id')) {
                $table->string('telegram_chat_id')->nullable()->after('avatar');
            }
            if (!Schema::hasColumn('users', 'telegram_username')) {
                $table->string('telegram_username')->nullable()->after('telegram_chat_id');
            }
            if (!Schema::hasColumn('users', 'telegram_connected_at')) {
                $table->timestamp('telegram_connected_at')->nullable()->after('telegram_username');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telegram_chat_id', 'telegram_username', 'telegram_connected_at']);
        });
    }
};
