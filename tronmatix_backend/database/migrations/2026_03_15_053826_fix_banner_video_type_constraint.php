<?php

// database/migrations/2026_03_16_000001_fix_banner_video_type_constraint.php
//
// WHY THIS EXISTS
// ───────────────
// The previous migration used ->enum() + ->after() which behave differently
// on PostgreSQL vs MySQL:
//
//   ->after('image')   → silently IGNORED on PostgreSQL (MySQL-only feature).
//                         The columns were added to the end of the table, which
//                         is fine — column order doesn't affect functionality.
//
//   ->enum([...])      → on PostgreSQL, Laravel creates the column as
//                         character varying(255) + a CHECK constraint.
//                         If the CHECK constraint is missing (visible in the DB
//                         viewer as plain varchar with no constraint), this
//                         migration recreates it safely.
//
// HOW TO RUN
// ──────────
//   php artisan migrate
//
// It is safe to run multiple times — the constraint is dropped first, then
// re-added, so duplicate-constraint errors cannot occur.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Name used for the CHECK constraint in PostgreSQL
    private string $constraintName = 'banners_video_type_check';

    private array $allowedTypes = ['upload', 'youtube', 'vimeo', 'facebook'];

    public function up(): void
    {
        // ── Only PostgreSQL needs the explicit CHECK constraint ────────────────
        if (DB::getDriverName() !== 'pgsql') {
            // MySQL / SQLite: enum is already enforced natively — nothing to do.
            return;
        }

        // ── 1. Ensure the column exists as varchar(255) ───────────────────────
        // (It should already exist from the previous migration.)
        if (! Schema::hasColumn('banners', 'video_type')) {
            DB::statement('ALTER TABLE banners ADD COLUMN video_type character varying(255) NULL');
        }

        // ── 2. Drop the old CHECK constraint if it already exists ─────────────
        // This prevents "constraint already exists" errors on re-runs.
        DB::statement("
            ALTER TABLE banners
            DROP CONSTRAINT IF EXISTS {$this->constraintName}
        ");

        // ── 3. Add the CHECK constraint with the full allowed-values list ──────
        $values = collect($this->allowedTypes)
            ->map(fn ($v) => "'{$v}'")
            ->implode(', ');

        DB::statement("
            ALTER TABLE banners
            ADD CONSTRAINT {$this->constraintName}
            CHECK (video_type IS NULL OR video_type IN ({$values}))
        ");

        // ── 4. Sanitise any existing rows that have an invalid value ──────────
        // Sets unrecognised values to NULL so the new constraint doesn't fail.
        $placeholders = implode(',', array_fill(0, count($this->allowedTypes), '?'));
        DB::statement("
            UPDATE banners
            SET    video_type = NULL
            WHERE  video_type IS NOT NULL
              AND  video_type NOT IN ({$placeholders})
        ", $this->allowedTypes);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("
            ALTER TABLE banners
            DROP CONSTRAINT IF EXISTS {$this->constraintName}
        ");
    }
};
