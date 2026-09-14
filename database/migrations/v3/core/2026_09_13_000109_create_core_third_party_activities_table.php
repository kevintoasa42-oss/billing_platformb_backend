<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound third-party economic activities with validity ranges. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.third_party_activities(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                third_party_id uuid NOT NULL,
                establishment_code text NOT NULL,
                activity_id text NOT NULL REFERENCES core.economic_activities(id),
                validity daterange NOT NULL,
                PRIMARY KEY(tenant_id,id),
                FOREIGN KEY(tenant_id,third_party_id) REFERENCES core.third_parties(tenant_id,id),
                EXCLUDE USING gist(tenant_id WITH =, third_party_id WITH =, establishment_code WITH =, activity_id WITH =, validity WITH &&)
            );

            ALTER TABLE core.third_party_activities ENABLE ROW LEVEL SECURITY;
            ALTER TABLE core.third_party_activities FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON core.third_party_activities;
            CREATE POLICY tenant_isolation ON core.third_party_activities
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS core.third_party_activities CASCADE;
            SQL);
    }
};
