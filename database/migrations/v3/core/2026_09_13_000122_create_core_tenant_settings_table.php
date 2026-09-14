<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound web settings (product, customer, tax, IAM). RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.tenant_settings(
                tenant_id uuid NOT NULL REFERENCES platform.tenants(id),
                product_settings jsonb NOT NULL DEFAULT '{}'::jsonb,
                customer_settings jsonb NOT NULL DEFAULT '{}'::jsonb,
                tax_settings jsonb NOT NULL DEFAULT '{}'::jsonb,
                payment_method_settings jsonb NOT NULL DEFAULT '[]'::jsonb,
                iam_settings jsonb NOT NULL DEFAULT '{"roles":[],"menus":[],"permissions":[]}'::jsonb,
                tax_catalog jsonb NOT NULL DEFAULT '{"types":[{"id":1,"name":"IVA 0%","percentage":"0.00","sri_code":"0","is_active":true},{"id":2,"name":"IVA 15%","percentage":"15.00","sri_code":"4","is_active":true}],"percentages":[{"id":1,"type_id":1,"percentage":"0.00","start_date":"2020-01-01","end_date":null,"is_active":true},{"id":2,"type_id":2,"percentage":"15.00","start_date":"2020-01-01","end_date":null,"is_active":true}]}'::jsonb,
                updated_at timestamptz NOT NULL DEFAULT now(),
                PRIMARY KEY(tenant_id)
            );

            ALTER TABLE core.tenant_settings ENABLE ROW LEVEL SECURITY;
            ALTER TABLE core.tenant_settings FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON core.tenant_settings;
            CREATE POLICY tenant_isolation ON core.tenant_settings
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS core.tenant_settings CASCADE;
            SQL);
    }
};
