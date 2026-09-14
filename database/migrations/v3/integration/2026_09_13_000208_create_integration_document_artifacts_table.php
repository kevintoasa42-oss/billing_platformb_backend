<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound document artifacts (XML, PDF, RIDE) stored as bytea with sha256. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS integration.document_artifacts(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                document_id uuid NOT NULL,
                kind text NOT NULL,
                content bytea NOT NULL,
                sha256 text NOT NULL,
                PRIMARY KEY(tenant_id,id),
                FOREIGN KEY(tenant_id,document_id) REFERENCES fiscal.documents(tenant_id,id),
                UNIQUE(tenant_id,document_id,kind)
            );

            ALTER TABLE integration.document_artifacts ENABLE ROW LEVEL SECURITY;
            ALTER TABLE integration.document_artifacts FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON integration.document_artifacts;
            CREATE POLICY tenant_isolation ON integration.document_artifacts
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS integration.document_artifacts CASCADE;
            SQL);
    }
};
