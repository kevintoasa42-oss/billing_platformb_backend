<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound fiscal outbox for async document operations. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS integration.fiscal_outbox(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                document_id uuid NOT NULL,
                operation text NOT NULL,
                status text NOT NULL DEFAULT 'pending',
                available_at timestamptz NOT NULL DEFAULT now(),
                PRIMARY KEY(tenant_id,id),
                FOREIGN KEY(tenant_id,document_id) REFERENCES fiscal.documents(tenant_id,id),
                UNIQUE(tenant_id,document_id,operation)
            );

            ALTER TABLE integration.fiscal_outbox ENABLE ROW LEVEL SECURITY;
            ALTER TABLE integration.fiscal_outbox FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON integration.fiscal_outbox;
            CREATE POLICY tenant_isolation ON integration.fiscal_outbox
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS integration.fiscal_outbox CASCADE;
            SQL);
    }
};
