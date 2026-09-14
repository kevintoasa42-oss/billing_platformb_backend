<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound company economic activities with validity ranges. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.company_activities(
                tenant_id uuid NOT NULL REFERENCES platform.tenants(id),
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                activity_id text NOT NULL REFERENCES core.economic_activities(id),
                validity daterange NOT NULL,
                is_primary boolean NOT NULL DEFAULT false,
                PRIMARY KEY(tenant_id,id),
                EXCLUDE USING gist(tenant_id WITH =, validity WITH &&) WHERE(is_primary)
            );

            ALTER TABLE core.company_activities ENABLE ROW LEVEL SECURITY;
            ALTER TABLE core.company_activities FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON core.company_activities;
            CREATE POLICY tenant_isolation ON core.company_activities
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS core.company_activities CASCADE;
            SQL);
    }
};
