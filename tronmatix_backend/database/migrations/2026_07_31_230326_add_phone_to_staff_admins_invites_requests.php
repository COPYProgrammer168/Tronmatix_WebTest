<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a phone column to the staff/admin/invite/request tables so the
     * forgot-password OTP flow and the staff page phone display have data.
     * Mirrors the existing users.phone pattern (string 30, nullable, no unique).
     */
    public function up(): void
    {
        foreach (['staff', 'admins', 'staff_invites', 'staff_requests'] as $table) {
            if (! Schema::hasColumn($table, 'phone')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('phone', 30)->nullable()->after('email');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['staff', 'admins', 'staff_invites', 'staff_requests'] as $table) {
            if (Schema::hasColumn($table, 'phone')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('phone');
                });
            }
        }
    }
};
