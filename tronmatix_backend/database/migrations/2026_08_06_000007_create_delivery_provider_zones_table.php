<?php

// database/migrations/2026_08_06_000007_create_delivery_provider_zones_table.php
//
// Per-zone fee/time rows for a delivery provider. The legacy flat
// `delivery_providers` table (delivery_zone_id, fee, estimated_time) stays
// untouched; this child table becomes the per-zone source of truth.
//
// A provider can serve phnom_penh and/or province with different fee + ETA.
// fee = NULL means "negotiable / varies" for that zone.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_provider_zones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('delivery_provider_id')
                ->constrained('delivery_providers')
                ->cascadeOnDelete();
            $table->enum('zone', ['phnom_penh', 'province']);
            $table->decimal('fee', 8, 2)->nullable()
                ->comment('NULL = negotiable / varies for this zone');
            $table->string('estimated_time', 100)->nullable()
                ->comment('e.g. "20-40 min" or "1-2 days"');

            $table->timestamps();

            $table->unique(['delivery_provider_id', 'zone'], 'dpz_provider_zone_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_provider_zones');
    }
};