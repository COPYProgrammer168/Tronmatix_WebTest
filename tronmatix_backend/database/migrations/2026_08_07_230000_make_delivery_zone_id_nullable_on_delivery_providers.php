<?php

// Make delivery_providers.delivery_zone_id nullable.
//
// The legacy flat delivery_zone_id/fee/estimated_time columns were NOT NULL
// when delivery_providers was created, but the system has since moved fee/ETA
// into the per-zone child table (delivery_provider_zones) which is now the
// source of truth. The admin create/edit controller (DeliveryProviderController
// -> store/update) deliberately stores NULL for these legacy columns. The
// seeder worked only because it supplied a real zone id; the admin "create
// provider" form hit a null-value violation. Making the column nullable aligns
// the schema with the controller's intent.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_providers', function (Blueprint $table) {
            $table->foreignId('delivery_zone_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('delivery_providers', function (Blueprint $table) {
            $table->foreignId('delivery_zone_id')->change();
        });
    }
};