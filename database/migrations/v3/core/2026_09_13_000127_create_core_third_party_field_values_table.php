<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound custom field values for third parties. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.third_party_field_values(
                tenant_id uuid NOT NULL REFERENCES platform.tenants(id),
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                third_party_id uuid NOT NULL,
                definition_id uuid NOT NULL,
                value jsonb NOT NULL,
                updated_by uuid,
                updated_at timestamptz NOT NULL DEFAULT now(),
                PRIMARY KEY(tenant_id,id),
                UNIQUE(tenant_id,third_party_id,definition_id),
                FOREIGN KEY(tenant_id,third_party_id) REFERENCES core.third_parties(tenant_id,id),
                FOREIGN KEY(tenant_id,definition_id) REFERENCES core.third_party_field_definitions(tenant_id,id),
                FOREIGN KEY(updated_by) REFERENCES auth.users(id)
            );

            ALTER TABLE core.third_party_field_values ENABLE ROW LEVEL SECURITY;
            ALTER TABLE core.third_party_field_values FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON core.third_party_field_values;
            CREATE POLICY tenant_isolation ON core.third_party_field_values
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS core.third_party_field_values CASCADE;
            SQL);
    }
};
