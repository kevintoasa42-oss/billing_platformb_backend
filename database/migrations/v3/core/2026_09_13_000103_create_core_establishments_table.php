<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound establishments (SRI 3-digit code). RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.establishments(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                company_id uuid NOT NULL,
                sri_code text NOT NULL CHECK(sri_code ~ '^[0-9]{3}$'),
                name text NOT NULL,
                PRIMARY KEY(tenant_id,id),
                UNIQUE(tenant_id,sri_code),
                FOREIGN KEY(tenant_id,company_id) REFERENCES core.companies(tenant_id,id)
            );

            ALTER TABLE core.establishments ENABLE ROW LEVEL SECURITY;
            ALTER TABLE core.establishments FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON core.establishments;
            CREATE POLICY tenant_isolation ON core.establishments
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS core.establishments CASCADE;
            SQL);
    }
};
