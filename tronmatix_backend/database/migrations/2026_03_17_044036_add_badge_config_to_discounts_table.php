<?php

// database/migrations/xxxx_xx_xx_add_badge_config_to_discounts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            // Stores the badge config object: { text, icon, bg, border, color }
            // null = no badge configured for this discount
            $table->json('badge_config')->nullable()->after('categories');
        });
    }

    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropColumn('badge_config');
        });
    }
};
