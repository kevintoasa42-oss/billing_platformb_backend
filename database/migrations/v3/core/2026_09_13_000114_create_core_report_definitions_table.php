<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound report definitions with versioned fields. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.report_definitions(
                tenant_id uuid NOT NULL REFERENCES platform.tenants(id),
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                name text NOT NULL,
                version int NOT NULL CHECK(version>0),
                fields jsonb NOT NULL,
                PRIMARY KEY(tenant_id,id),
                UNIQUE(tenant_id,name,version)
            );

            ALTER TABLE core.report_definitions ENABLE ROW LEVEL SECURITY;
            ALTER TABLE core.report_definitions FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON core.report_definitions;
            CREATE POLICY tenant_isolation ON core.report_definitions
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS core.report_definitions CASCADE;
            SQL);
    }
};
