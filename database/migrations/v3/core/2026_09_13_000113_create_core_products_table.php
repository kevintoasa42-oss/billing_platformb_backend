<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound products with economic activity and unit price. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.products(
                tenant_id uuid NOT NULL REFERENCES platform.tenants(id),
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                name text NOT NULL,
                activity_id text NOT NULL REFERENCES core.economic_activities(id),
                unit_price numeric(18,6) NOT NULL CHECK(unit_price>=0),
                PRIMARY KEY(tenant_id,id)
            );

            ALTER TABLE core.products ENABLE ROW LEVEL SECURITY;
            ALTER TABLE core.products FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON core.products;
            CREATE POLICY tenant_isolation ON core.products
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS core.products CASCADE;
            SQL);
    }
};
