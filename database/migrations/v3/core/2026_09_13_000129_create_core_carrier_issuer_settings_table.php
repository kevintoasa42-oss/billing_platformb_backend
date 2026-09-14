<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound default carrier issuer mode. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.carrier_issuer_settings(
                tenant_id uuid PRIMARY KEY REFERENCES platform.tenants(id),
                mode text NOT NULL DEFAULT 'operator' CHECK(mode IN ('operator','partner')),
                updated_by uuid REFERENCES auth.users(id),
                updated_at timestamptz NOT NULL DEFAULT now()
            );

            ALTER TABLE core.carrier_issuer_settings ENABLE ROW LEVEL SECURITY;
            ALTER TABLE core.carrier_issuer_settings FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON core.carrier_issuer_settings;
            CREATE POLICY tenant_isolation ON core.carrier_issuer_settings
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS core.carrier_issuer_settings CASCADE;
            SQL);
    }
};
