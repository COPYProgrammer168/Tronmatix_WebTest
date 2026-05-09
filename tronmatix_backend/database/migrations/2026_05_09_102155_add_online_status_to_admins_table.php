<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable()->after('last_login_at');
            $table->enum('online_status', ['online', 'offline'])->default('offline')->after('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn(['last_seen_at', 'online_status']);
        });
    }
};
