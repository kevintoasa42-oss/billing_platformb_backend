<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound companies (RUC holders). RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.companies(
                tenant_id uuid NOT NULL REFERENCES platform.tenants(id),
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                name text NOT NULL,
                ruc text NOT NULL,
                PRIMARY KEY(tenant_id,id),
                UNIQUE(tenant_id)
            );

            ALTER TABLE core.companies ENABLE ROW LEVEL SECURITY;
            ALTER TABLE core.companies FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON core.companies;
            CREATE POLICY tenant_isolation ON core.companies
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS core.companies CASCADE;
            SQL);
    }
};
