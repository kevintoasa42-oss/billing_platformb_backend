<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound append-only merge snapshots for third-party identity consolidation. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.third_party_merge_snapshots(
                tenant_id uuid NOT NULL REFERENCES platform.tenants(id),
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                canonical_third_party_id uuid NOT NULL,
                merged_third_party_ids uuid[] NOT NULL,
                conflicts jsonb NOT NULL DEFAULT '[]'::jsonb,
                snapshot jsonb NOT NULL,
                dry_run boolean NOT NULL DEFAULT true,
                created_at timestamptz NOT NULL DEFAULT now(),
                PRIMARY KEY(tenant_id,id),
                FOREIGN KEY(tenant_id,canonical_third_party_id) REFERENCES core.third_parties(tenant_id,id)
            );

            ALTER TABLE core.third_party_merge_snapshots ENABLE ROW LEVEL SECURITY;
            ALTER TABLE core.third_party_merge_snapshots FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON core.third_party_merge_snapshots;
            CREATE POLICY tenant_isolation ON core.third_party_merge_snapshots
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS core.third_party_merge_snapshots CASCADE;
            SQL);
    }
};
