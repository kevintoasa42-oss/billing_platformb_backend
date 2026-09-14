<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound outbox for third-party domain events with retry support. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS integration.third_party_outbox(
                tenant_id uuid NOT NULL REFERENCES platform.tenants(id),
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                aggregate_id uuid NOT NULL,
                event_type text NOT NULL,
                payload jsonb NOT NULL,
                status text NOT NULL DEFAULT 'pending' CHECK(status IN ('pending','processing','completed','failed')),
                attempts integer NOT NULL DEFAULT 0,
                available_at timestamptz NOT NULL DEFAULT now(),
                locked_until timestamptz,
                last_error text,
                created_at timestamptz NOT NULL DEFAULT now(),
                updated_at timestamptz NOT NULL DEFAULT now(),
                PRIMARY KEY(tenant_id,id),
                UNIQUE(tenant_id,aggregate_id,event_type),
                FOREIGN KEY(tenant_id,aggregate_id) REFERENCES core.third_parties(tenant_id,id)
            );

            CREATE INDEX IF NOT EXISTS integration_third_party_outbox_pending
                ON integration.third_party_outbox(status,available_at,created_at)
                WHERE status IN ('pending','failed');

            ALTER TABLE integration.third_party_outbox ENABLE ROW LEVEL SECURITY;
            ALTER TABLE integration.third_party_outbox FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON integration.third_party_outbox;
            CREATE POLICY tenant_isolation ON integration.third_party_outbox
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS integration.third_party_outbox CASCADE;
            SQL);
    }
};
