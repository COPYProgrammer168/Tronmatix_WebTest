<?php

// database/migrations/xxxx_add_lat_lng_to_user_locations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_locations', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable()->after('note');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
            $table->text('map_address')->nullable()->after('lng'); // full address from reverse geocode
        });

        // Also add to orders table so admin can see pin even after location changes
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('delivery_lat', 10, 7)->nullable()->after('location');
            $table->decimal('delivery_lng', 10, 7)->nullable()->after('delivery_lat');
            $table->text('delivery_map_address')->nullable()->after('delivery_lng');
        });
    }

    public function down(): void
    {
        Schema::table('user_locations', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng', 'map_address']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_lat', 'delivery_lng', 'delivery_map_address']);
        });
    }
};
