<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound vehicles identified by plate. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.vehicles(
                tenant_id uuid NOT NULL REFERENCES platform.tenants(id),
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                plate text NOT NULL,
                PRIMARY KEY(tenant_id,id),
                UNIQUE(tenant_id,plate)
            );

            ALTER TABLE core.vehicles ENABLE ROW LEVEL SECURITY;
            ALTER TABLE core.vehicles FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON core.vehicles;
            CREATE POLICY tenant_isolation ON core.vehicles
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS core.vehicles CASCADE;
            SQL);
    }
};
