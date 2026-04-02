<?php

// database/migrations/xxxx_xx_xx_add_telegram_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Raw telegram user ID — used as chat_id for DMs
            $table->string('telegram_chat_id')->nullable()->after('phone');

            // @username (optional, not all Telegram accounts have one)
            $table->string('telegram_username')->nullable()->after('telegram_chat_id');

            // When the user first connected their Telegram
            $table->timestamp('telegram_connected_at')->nullable()->after('telegram_username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telegram_chat_id', 'telegram_username', 'telegram_connected_at']);
        });
    }
};
