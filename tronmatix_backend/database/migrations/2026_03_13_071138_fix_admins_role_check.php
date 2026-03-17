<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old restrictive constraint
        DB::statement('ALTER TABLE admins DROP CONSTRAINT IF EXISTS admins_role_check');

        // Add updated constraint with all 4 roles
        DB::statement("
            ALTER TABLE admins
            ADD CONSTRAINT admins_role_check
            CHECK (role IN ('superadmin', 'admin', 'editor', 'viewer'))
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE admins DROP CONSTRAINT IF EXISTS admins_role_check');

        // Restore original constraint (without viewer)
        DB::statement("
            ALTER TABLE admins
            ADD CONSTRAINT admins_role_check
            CHECK (role IN ('superadmin', 'admin', 'editor'))
        ");
    }
};
