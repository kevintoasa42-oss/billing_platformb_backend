<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Global catalog of economic activities. Not tenant-bound (no RLS).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.economic_activities(
                id text PRIMARY KEY,
                name text NOT NULL,
                catalog_version text NOT NULL CHECK(catalog_version='synthetic-lab-v1')
            );
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS core.economic_activities CASCADE;
            SQL);
    }
};
