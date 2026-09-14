<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound carrier electronic-signature metadata. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.carrier_signatures(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                carrier_company_id uuid NOT NULL,
                status text NOT NULL DEFAULT 'missing' CHECK(status IN ('missing','active','expired','revoked','invalid')),
                certificate_fingerprint text,
                certificate_serial text,
                certificate_subject text,
                valid_from timestamptz,
                valid_until timestamptz,
                key_reference text,
                key_version bigint,
                replaced_at timestamptz,
                updated_by uuid,
                created_at timestamptz NOT NULL DEFAULT now(),
                updated_at timestamptz NOT NULL DEFAULT now(),
                PRIMARY KEY(tenant_id,id),
                UNIQUE(tenant_id,carrier_company_id),
                FOREIGN KEY(tenant_id,carrier_company_id) REFERENCES core.carrier_companies(tenant_id,id),
                FOREIGN KEY(updated_by) REFERENCES auth.users(id),
                CHECK(length(coalesce(certificate_fingerprint,'')) <= 128),
                CHECK(length(coalesce(key_reference,'')) <= 255)
            );

            ALTER TABLE core.carrier_signatures ENABLE ROW LEVEL SECURITY;
            ALTER TABLE core.carrier_signatures FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON core.carrier_signatures;
            CREATE POLICY tenant_isolation ON core.carrier_signatures
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS core.carrier_signatures CASCADE;
            SQL);
    }
};
