<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound idempotent carrier onboarding requests. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.carrier_onboarding_requests(
                tenant_id uuid NOT NULL REFERENCES platform.tenants(id),
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                idempotency_key text NOT NULL,
                request_hash text NOT NULL,
                third_party_id uuid NOT NULL,
                response jsonb NOT NULL,
                created_at timestamptz NOT NULL DEFAULT now(),
                PRIMARY KEY(tenant_id,id),
                UNIQUE(tenant_id,idempotency_key),
                FOREIGN KEY(tenant_id,third_party_id) REFERENCES core.third_parties(tenant_id,id),
                CHECK(idempotency_key ~ '^[A-Za-z0-9_-]{8,100}$')
            );

            ALTER TABLE core.carrier_onboarding_requests ENABLE ROW LEVEL SECURITY;
            ALTER TABLE core.carrier_onboarding_requests FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON core.carrier_onboarding_requests;
            CREATE POLICY tenant_isolation ON core.carrier_onboarding_requests
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS core.carrier_onboarding_requests CASCADE;
            SQL);
    }
};
