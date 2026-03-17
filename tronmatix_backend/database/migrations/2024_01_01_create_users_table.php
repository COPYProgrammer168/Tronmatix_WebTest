<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('username')->unique();
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');

                // ── Profile ──────────────────────────────────────────────────
                $table->string('phone', 30)->nullable()
                    ->comment('Contact number — copied to shipping on checkout');
                $table->string('avatar')->nullable()
                    ->comment('Profile picture path under storage/ or external URL');

                // ── Role & access ─────────────────────────────────────────────
                $table->string('role', 30)->default('customer')
                    ->comment('customer | vip | reseller | banned');
                $table->boolean('is_banned')->default(false)
                    ->comment('Quick ban flag — role="banned" is also checked');

                // ── 2FA (optional) ────────────────────────────────────────────
                $table->string('two_factor_secret')->nullable()
                    ->comment('TOTP secret — null when 2FA not set up');
                $table->boolean('two_factor_enabled')->default(false);
                $table->timestamp('two_factor_confirmed_at')->nullable()
                    ->comment('Set when user completes 2FA confirmation step');

                $table->rememberToken();
                $table->timestamps();

                $table->index('role');
                $table->index('is_banned');
                $table->index('email');
            });
        } else {
            // Patch existing table safely
            Schema::table('users', function (Blueprint $table) {
                $cols = Schema::getColumnListing('users');
                if (! in_array('phone', $cols)) {
                    $table->string('phone', 30)->nullable();
                }
                if (! in_array('avatar', $cols)) {
                    $table->string('avatar')->nullable();
                }
                if (! in_array('role', $cols)) {
                    $table->string('role', 30)->default('customer');
                }
                if (! in_array('is_banned', $cols)) {
                    $table->boolean('is_banned')->default(false);
                }
                if (! in_array('two_factor_secret', $cols)) {
                    $table->string('two_factor_secret')->nullable();
                }
                if (! in_array('two_factor_enabled', $cols)) {
                    $table->boolean('two_factor_enabled')->default(false);
                }
                if (! in_array('two_factor_confirmed_at', $cols)) {
                    $table->timestamp('two_factor_confirmed_at')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
