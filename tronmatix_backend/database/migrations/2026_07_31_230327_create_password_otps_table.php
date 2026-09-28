<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phone-based password reset OTPs for the dashboard (admin/staff).
     * Mirrors the TelegramConnectionToken pattern: short-lived single-use codes.
     */
    public function up(): void
    {
        Schema::create('password_otps', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 30);
            $table->string('mode', 10)->default('staff'); // admin | staff
            $table->string('otp', 6);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['phone', 'mode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_otps');
    }
};
