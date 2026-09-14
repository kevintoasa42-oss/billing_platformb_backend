<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound emission points (SRI 3-digit code per establishment). RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.emission_points(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                establishment_id uuid NOT NULL,
                sri_code text NOT NULL CHECK(sri_code ~ '^[0-9]{3}$'),
                PRIMARY KEY(tenant_id,id),
                UNIQUE(tenant_id,establishment_id,sri_code),
                FOREIGN KEY(tenant_id,establishment_id) REFERENCES core.establishments(tenant_id,id)
            );

            ALTER TABLE core.emission_points ENABLE ROW LEVEL SECURITY;
            ALTER TABLE core.emission_points FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON core.emission_points;
            CREATE POLICY tenant_isolation ON core.emission_points
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS core.emission_points CASCADE;
            SQL);
    }
};
