<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound append-only third-party identity aliases from source systems. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.third_party_identity_aliases(
                tenant_id uuid NOT NULL REFERENCES platform.tenants(id),
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                third_party_id uuid NOT NULL,
                source_system text NOT NULL,
                source_id text NOT NULL,
                source_snapshot jsonb NOT NULL DEFAULT '{}'::jsonb,
                created_at timestamptz NOT NULL DEFAULT now(),
                PRIMARY KEY(tenant_id,id),
                UNIQUE(tenant_id,source_system,source_id),
                FOREIGN KEY(tenant_id,third_party_id) REFERENCES core.third_parties(tenant_id,id)
            );

            ALTER TABLE core.third_party_identity_aliases ENABLE ROW LEVEL SECURITY;
            ALTER TABLE core.third_party_identity_aliases FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON core.third_party_identity_aliases;
            CREATE POLICY tenant_isolation ON core.third_party_identity_aliases
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS core.third_party_identity_aliases CASCADE;
            SQL);
    }
};
