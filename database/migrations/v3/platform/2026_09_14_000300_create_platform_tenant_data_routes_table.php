<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Create platform.tenant_data_routes table - used by invoice readiness check.
 * RLS enabled for tenant isolation.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS platform.tenant_data_routes (
                tenant_id uuid NOT NULL,
                state text NOT NULL DEFAULT 'consolidated' CHECK(state IN ('consolidated','standalone','migration')),
                frozen boolean NOT NULL DEFAULT false,
                updated_at timestamptz NOT NULL DEFAULT now(),
                PRIMARY KEY (tenant_id)
            );

            ALTER TABLE platform.tenant_data_routes ENABLE ROW LEVEL SECURITY;
            ALTER TABLE platform.tenant_data_routes FORCE ROW LEVEL SECURITY;

            CREATE POLICY tenant_data_routes_tenant_isolation ON platform.tenant_data_routes
                USING (tenant_id = current_setting('app.tenant_id', true)::uuid);
        SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared("DROP TABLE IF EXISTS platform.tenant_data_routes;");
    }
};
