<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('province_id')->nullable()->after('location_id')->constrained('provinces')->nullOnDelete();
            $table->foreignId('delivery_provider_id')->nullable()->after('province_id')->constrained('delivery_providers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropForeign(['delivery_provider_id']);
            $table->dropColumn(['province_id', 'delivery_provider_id']);
        });
    }
};
