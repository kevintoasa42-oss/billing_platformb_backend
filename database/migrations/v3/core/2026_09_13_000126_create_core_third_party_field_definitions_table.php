<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound custom field definitions for third parties. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.third_party_field_definitions(
                tenant_id uuid NOT NULL REFERENCES platform.tenants(id),
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                code text NOT NULL,
                label text NOT NULL,
                scope text NOT NULL DEFAULT 'both' CHECK(scope IN ('customer','carrier','both')),
                data_type text NOT NULL DEFAULT 'text' CHECK(data_type IN ('text','number','date','boolean','select')),
                validation jsonb NOT NULL DEFAULT '{}'::jsonb,
                sort_order integer NOT NULL DEFAULT 0,
                is_required boolean NOT NULL DEFAULT false,
                is_active boolean NOT NULL DEFAULT true,
                created_at timestamptz NOT NULL DEFAULT now(),
                updated_at timestamptz NOT NULL DEFAULT now(),
                PRIMARY KEY(tenant_id,id),
                UNIQUE(tenant_id,code),
                CHECK(code ~ '^[a-z][a-z0-9_]{1,80}$'),
                CHECK(length(trim(label)) BETWEEN 1 AND 160)
            );

            CREATE INDEX IF NOT EXISTS core_third_party_field_definitions_active
                ON core.third_party_field_definitions(tenant_id,is_active,scope,sort_order);

            ALTER TABLE core.third_party_field_definitions ENABLE ROW LEVEL SECURITY;
            ALTER TABLE core.third_party_field_definitions FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON core.third_party_field_definitions;
            CREATE POLICY tenant_isolation ON core.third_party_field_definitions
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS core.third_party_field_definitions CASCADE;
            SQL);
    }
};
