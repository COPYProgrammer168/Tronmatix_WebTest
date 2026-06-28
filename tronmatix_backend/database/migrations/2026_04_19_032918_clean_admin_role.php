<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Remove any old mixed-role check constraint and set admin-only roles
        DB::statement('ALTER TABLE admins DROP CONSTRAINT IF EXISTS admins_role_check');
        DB::statement("
            ALTER TABLE admins
            ADD CONSTRAINT admins_role_check
            CHECK (role IN ('superadmin', 'admin'))
        ");

        // 2. Drop leftover columns from the old single-table design (safe if they exist)
        Schema::table('admins', function (Blueprint $table) {
            $columns = ['is_pending', 'request_note'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('admins', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down(): void
    {
        // Restore the broader role constraint
        DB::statement('ALTER TABLE admins DROP CONSTRAINT IF EXISTS admins_role_check');
        DB::statement("
            ALTER TABLE admins
            ADD CONSTRAINT admins_role_check
            CHECK (role IN ('superadmin', 'admin', 'editor', 'seller', 'delivery', 'developer'))
        ");

        Schema::table('admins', function (Blueprint $table) {
            $table->boolean('is_pending')->default(false);
            $table->text('request_note')->nullable();
        });
    }
};
