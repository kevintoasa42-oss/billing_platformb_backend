<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound third-party role assignments (customer, supplier, member, carrier). RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.third_party_roles(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                third_party_id uuid NOT NULL,
                role text NOT NULL CHECK(role IN ('customer','supplier','member','carrier')),
                PRIMARY KEY(tenant_id,id),
                FOREIGN KEY(tenant_id,third_party_id) REFERENCES core.third_parties(tenant_id,id),
                UNIQUE(tenant_id,third_party_id,role)
            );

            CREATE INDEX IF NOT EXISTS core_third_party_roles_role_lookup
                ON core.third_party_roles (tenant_id, role, third_party_id);

            ALTER TABLE core.third_party_roles ENABLE ROW LEVEL SECURITY;
            ALTER TABLE core.third_party_roles FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON core.third_party_roles;
            CREATE POLICY tenant_isolation ON core.third_party_roles
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS core.third_party_roles CASCADE;
            SQL);
    }
};
