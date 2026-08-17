<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('staff', 'online_status')) {
            Schema::table('staff', function (Blueprint $table) {
                $table->timestamp('last_seen_at')->nullable()->after('last_login_at');
                $table->string('online_status')->default('offline')->after('last_seen_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('staff', 'online_status')) {
            Schema::table('staff', function (Blueprint $table) {
                $table->dropColumn(['online_status', 'last_seen_at']);
            });
        }
    }
};
