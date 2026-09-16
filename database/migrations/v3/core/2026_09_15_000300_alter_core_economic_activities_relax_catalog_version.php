<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Relax the catalog_version check on core.economic_activities to allow
 * the official SRI CIIU 4.1 catalog alongside the synthetic lab catalog.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            ALTER TABLE core.economic_activities
                DROP CONSTRAINT IF EXISTS economic_activities_catalog_version_check;

            ALTER TABLE core.economic_activities
                ADD CONSTRAINT economic_activities_catalog_version_check
                CHECK(catalog_version IN ('synthetic-lab-v1', 'CIIU-4.1'));
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            ALTER TABLE core.economic_activities
                DROP CONSTRAINT IF EXISTS economic_activities_catalog_version_check;

            ALTER TABLE core.economic_activities
                ADD CONSTRAINT economic_activities_catalog_version_check
                CHECK(catalog_version='synthetic-lab-v1');
            SQL);
    }
};
