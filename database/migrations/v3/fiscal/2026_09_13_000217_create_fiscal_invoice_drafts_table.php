<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound invoice drafts for the V3 web editor. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS fiscal.invoice_drafts(
                tenant_id uuid NOT NULL REFERENCES platform.tenants(id),
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                public_id uuid NOT NULL DEFAULT gen_random_uuid(),
                user_id uuid NOT NULL REFERENCES auth.users(id),
                revision integer NOT NULL DEFAULT 1 CHECK(revision > 0),
                payload jsonb NOT NULL DEFAULT '{}'::jsonb,
                expires_at timestamptz NOT NULL DEFAULT (now() + interval '30 days'),
                created_at timestamptz NOT NULL DEFAULT now(),
                updated_at timestamptz NOT NULL DEFAULT now(),
                PRIMARY KEY(tenant_id,id),
                UNIQUE(tenant_id,public_id),
                UNIQUE(tenant_id,user_id)
            );

            CREATE INDEX IF NOT EXISTS fiscal_invoice_drafts_user
                ON fiscal.invoice_drafts(tenant_id,user_id,updated_at DESC);

            ALTER TABLE fiscal.invoice_drafts ENABLE ROW LEVEL SECURITY;
            ALTER TABLE fiscal.invoice_drafts FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON fiscal.invoice_drafts;
            CREATE POLICY tenant_isolation ON fiscal.invoice_drafts
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS fiscal.invoice_drafts CASCADE;
            SQL);
    }
};
