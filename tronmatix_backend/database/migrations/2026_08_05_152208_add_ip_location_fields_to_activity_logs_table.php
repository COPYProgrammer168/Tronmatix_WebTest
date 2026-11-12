<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('ip_country')->nullable()->after('ip_address');
            $table->string('ip_region')->nullable()->after('ip_country');
            $table->string('ip_city')->nullable()->after('ip_region');
            $table->string('ip_isp')->nullable()->after('ip_city');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(['ip_country', 'ip_region', 'ip_city', 'ip_isp']);
        });
    }
};
