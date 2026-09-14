<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound establishment economic activities with validity ranges. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.establishment_activities(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                establishment_id uuid NOT NULL,
                activity_id text NOT NULL REFERENCES core.economic_activities(id),
                validity daterange NOT NULL,
                PRIMARY KEY(tenant_id,id),
                FOREIGN KEY(tenant_id,establishment_id) REFERENCES core.establishments(tenant_id,id),
                EXCLUDE USING gist(tenant_id WITH =, establishment_id WITH =, activity_id WITH =, validity WITH &&)
            );

            ALTER TABLE core.establishment_activities ENABLE ROW LEVEL SECURITY;
            ALTER TABLE core.establishment_activities FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON core.establishment_activities;
            CREATE POLICY tenant_isolation ON core.establishment_activities
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS core.establishment_activities CASCADE;
            SQL);
    }
};
