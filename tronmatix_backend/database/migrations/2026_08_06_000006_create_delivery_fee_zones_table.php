<?php

// database/migrations/2026_08_06_000006_create_delivery_fee_zones_table.php
//
// Distance-based delivery fee tiers. Separate from the legacy `delivery_zones`
// (id/name/slug → delivery_providers mapping) so the existing
// province→zone→provider flow is untouched.
//
// Rows are matched against a reverse-geocoded province name (Nominatim) via
// `province_match`. A row with province_match = NULL is the default/fallback
// tier that catches any unmatched province.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_fee_zones', function (Blueprint $table) {
            $table->id();

            $table->string('zone_name');
            $table->string('province_match')->nullable()
                ->comment('Matched against geocoded province name; NULL = default/fallback zone');

            $table->decimal('base_fee', 8, 2);
            $table->decimal('free_km', 5, 2)->default(0)
                ->comment('Distance (km) included in the base fee');
            $table->decimal('per_km_rate', 8, 2)
                ->comment('Fee per km beyond free_km');
            $table->decimal('max_distance_km', 6, 2)->nullable()
                ->comment('Max deliverable distance; NULL = no limit');
            $table->decimal('road_factor', 3, 2)->default(1.3)
                ->comment('Straight-line distance multiplier to approximate road distance');
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('province_match');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_fee_zones');
    }
};