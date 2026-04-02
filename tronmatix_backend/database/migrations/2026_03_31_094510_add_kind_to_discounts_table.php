<?php

// database/migrations/2025_xx_xx_add_kind_to_discounts_table.php
// Run: php artisan migrate

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            // 'code'  = customer types code at checkout (default, backward-compatible)
            // 'badge' = auto-shown on product cards, no code entry needed
            $table->string('kind', 10)->default('code')->after('code');
        });
    }

    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropColumn('kind');
        });
    }
};
