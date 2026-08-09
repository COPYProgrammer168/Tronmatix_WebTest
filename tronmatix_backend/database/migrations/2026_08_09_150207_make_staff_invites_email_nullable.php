<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('staff_invites', function (Blueprint $table) {
            // Phone-only invites (no email) are allowed — drop the NOT NULL +
            // unique index so email can be NULL. Multiple NULL emails are legal
            // in PostgreSQL (NULLs are distinct in unique indexes), but we drop
            // the unique index anyway to keep the intent clear.
            $table->dropUnique(['email']);
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_invites', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
            $table->unique('email');
        });
    }
};
