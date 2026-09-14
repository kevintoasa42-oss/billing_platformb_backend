<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound carrier-vehicle assignments with validity ranges. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.carrier_vehicle_assignments(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                affiliation_id uuid NOT NULL,
                vehicle_id uuid NOT NULL,
                validity daterange NOT NULL,
                PRIMARY KEY(tenant_id,id),
                FOREIGN KEY(tenant_id,affiliation_id) REFERENCES core.carrier_affiliations(tenant_id,id),
                FOREIGN KEY(tenant_id,vehicle_id) REFERENCES core.vehicles(tenant_id,id),
                EXCLUDE USING gist(tenant_id WITH =, vehicle_id WITH =, validity WITH &&)
            );

            ALTER TABLE core.carrier_vehicle_assignments ENABLE ROW LEVEL SECURITY;
            ALTER TABLE core.carrier_vehicle_assignments FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON core.carrier_vehicle_assignments;
            CREATE POLICY tenant_isolation ON core.carrier_vehicle_assignments
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS core.carrier_vehicle_assignments CASCADE;
            SQL);
    }
};
