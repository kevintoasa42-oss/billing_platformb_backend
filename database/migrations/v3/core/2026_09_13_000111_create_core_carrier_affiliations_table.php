<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound carrier affiliations (third-party + validity range). RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.carrier_affiliations(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                third_party_id uuid NOT NULL,
                validity daterange NOT NULL,
                PRIMARY KEY(tenant_id,id),
                FOREIGN KEY(tenant_id,third_party_id) REFERENCES core.third_parties(tenant_id,id)
            );

            ALTER TABLE core.carrier_affiliations ENABLE ROW LEVEL SECURITY;
            ALTER TABLE core.carrier_affiliations FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON core.carrier_affiliations;
            CREATE POLICY tenant_isolation ON core.carrier_affiliations
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS core.carrier_affiliations CASCADE;
            SQL);
    }
};
