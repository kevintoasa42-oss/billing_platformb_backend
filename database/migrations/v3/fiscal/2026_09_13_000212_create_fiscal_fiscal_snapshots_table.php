<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound immutable fiscal snapshots for XML/RIDE reproduction. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS fiscal.fiscal_snapshots(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                document_id uuid NOT NULL,
                snapshot jsonb NOT NULL,
                PRIMARY KEY(tenant_id,id),
                FOREIGN KEY(tenant_id,document_id) REFERENCES fiscal.documents(tenant_id,id)
            );

            ALTER TABLE fiscal.fiscal_snapshots ENABLE ROW LEVEL SECURITY;
            ALTER TABLE fiscal.fiscal_snapshots FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON fiscal.fiscal_snapshots;
            CREATE POLICY tenant_isolation ON fiscal.fiscal_snapshots
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS fiscal.fiscal_snapshots CASCADE;
            SQL);
    }
};
