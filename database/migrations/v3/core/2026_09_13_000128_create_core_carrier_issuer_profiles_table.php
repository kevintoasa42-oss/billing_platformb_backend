<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound carrier issuer profiles (operator or partner mode). RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.carrier_issuer_profiles(
                tenant_id uuid NOT NULL REFERENCES platform.tenants(id),
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                carrier_company_id uuid NOT NULL,
                mode text NOT NULL DEFAULT 'operator' CHECK(mode IN ('operator','partner')),
                operator_company_id uuid,
                operator_establishment_id uuid,
                operator_emission_point_id uuid,
                partner_ruc text,
                partner_establishment_code text,
                partner_emission_point_code text,
                next_sequential bigint,
                key_reference text,
                key_version bigint,
                created_at timestamptz NOT NULL DEFAULT now(),
                updated_at timestamptz NOT NULL DEFAULT now(),
                PRIMARY KEY(tenant_id,id),
                UNIQUE(tenant_id,carrier_company_id),
                FOREIGN KEY(tenant_id,carrier_company_id) REFERENCES core.carrier_companies(tenant_id,id),
                FOREIGN KEY(tenant_id,operator_company_id) REFERENCES core.companies(tenant_id,id),
                FOREIGN KEY(tenant_id,operator_establishment_id) REFERENCES core.establishments(tenant_id,id),
                FOREIGN KEY(tenant_id,operator_emission_point_id) REFERENCES core.emission_points(tenant_id,id),
                CHECK((mode='operator' AND operator_company_id IS NOT NULL AND operator_establishment_id IS NOT NULL AND operator_emission_point_id IS NOT NULL AND partner_ruc IS NULL)
                   OR (mode='partner' AND partner_ruc ~ '^[0-9]{13}$' AND partner_establishment_code ~ '^[0-9]{3}$' AND partner_emission_point_code ~ '^[0-9]{3}$' AND next_sequential BETWEEN 1 AND 999999999 AND length(trim(coalesce(key_reference,''))) > 0 AND operator_company_id IS NULL AND operator_establishment_id IS NULL AND operator_emission_point_id IS NULL))
            );

            ALTER TABLE core.carrier_issuer_profiles ENABLE ROW LEVEL SECURITY;
            ALTER TABLE core.carrier_issuer_profiles FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON core.carrier_issuer_profiles;
            CREATE POLICY tenant_isolation ON core.carrier_issuer_profiles
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS core.carrier_issuer_profiles CASCADE;
            SQL);
    }
};
