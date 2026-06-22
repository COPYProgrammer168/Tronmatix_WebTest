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

                // ── Telegram & Social ─────────────────────────────────────────
                $table->string('telegram_chat_id')->nullable();
                $table->string('telegram_username')->nullable();
                $table->timestamp('telegram_connected_at')->nullable();
                $table->string('google_id')->nullable();

                $table->rememberToken();
                $table->timestamps();

                $table->index('role');
                $table->index('is_banned');
                $table->index('email');
            });
        } else {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'phone')) {
                    $table->string('phone', 30)->nullable();
                }
                if (! Schema::hasColumn('users', 'avatar')) {
                    $table->string('avatar')->nullable();
                }
                if (! Schema::hasColumn('users', 'role')) {
                    $table->string('role', 30)->default('customer');
                }
                if (! Schema::hasColumn('users', 'is_banned')) {
                    $table->boolean('is_banned')->default(false);
                }
                if (! Schema::hasColumn('users', 'two_factor_secret')) {
                    $table->string('two_factor_secret')->nullable();
                }
                if (! Schema::hasColumn('users', 'two_factor_enabled')) {
                    $table->boolean('two_factor_enabled')->default(false);
                }
                if (! Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                    $table->timestamp('two_factor_confirmed_at')->nullable();
                }
                if (! Schema::hasColumn('users', 'telegram_chat_id')) {
                    $table->string('telegram_chat_id')->nullable();
                }
                if (! Schema::hasColumn('users', 'telegram_username')) {
                    $table->string('telegram_username')->nullable();
                }
                if (! Schema::hasColumn('users', 'telegram_connected_at')) {
                    $table->timestamp('telegram_connected_at')->nullable();
                }
                if (! Schema::hasColumn('users', 'google_id')) {
                    $table->string('google_id')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
