<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound third parties (customers, suppliers, carriers). RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.third_parties(
                tenant_id uuid NOT NULL REFERENCES platform.tenants(id),
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                name text NOT NULL,
                identification text NOT NULL,
                identification_type text NOT NULL CHECK(identification_type IN ('04','05','06','07')),
                person_type text CHECK(person_type IS NULL OR person_type IN ('natural','juridical')),
                must_invoice boolean NOT NULL DEFAULT true,
                PRIMARY KEY(tenant_id,id)
            );

            CREATE UNIQUE INDEX IF NOT EXISTS core_third_parties_canonical_identification
                ON core.third_parties (
                    tenant_id,
                    identification_type,
                    upper(regexp_replace(trim(identification), '[[:space:]]+', '', 'g'))
                );

            CREATE INDEX IF NOT EXISTS core_third_parties_identification_lookup
                ON core.third_parties (
                    tenant_id,
                    identification_type,
                    upper(regexp_replace(trim(identification), '[[:space:]]+', '', 'g')),
                    id
                );

            ALTER TABLE core.third_parties ENABLE ROW LEVEL SECURITY;
            ALTER TABLE core.third_parties FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON core.third_parties;
            CREATE POLICY tenant_isolation ON core.third_parties
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS core.third_parties CASCADE;
            SQL);
    }
};
