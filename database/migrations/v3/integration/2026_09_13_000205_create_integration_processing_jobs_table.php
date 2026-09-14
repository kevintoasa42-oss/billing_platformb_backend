<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound processing jobs for the fiscal document pipeline (xml, signature, send, authorization, pdf, delivery). RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS integration.processing_jobs(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                document_id uuid NOT NULL,
                operation text NOT NULL CHECK(operation IN ('xml','signature','send','authorization','authorized_xml','pdf','delivery')),
                predecessor_id uuid,
                status text NOT NULL DEFAULT 'pending' CHECK(status IN ('pending','running','completed','dead')),
                available_at timestamptz NOT NULL DEFAULT now(),
                lease_until timestamptz,
                fencing_token bigint NOT NULL DEFAULT 0,
                writer_epoch bigint NOT NULL,
                attempts int NOT NULL DEFAULT 0,
                max_attempts int NOT NULL DEFAULT 3,
                PRIMARY KEY(tenant_id,id),
                FOREIGN KEY(tenant_id,document_id) REFERENCES fiscal.documents(tenant_id,id),
                FOREIGN KEY(tenant_id,predecessor_id) REFERENCES integration.processing_jobs(tenant_id,id),
                UNIQUE(tenant_id,document_id,operation)
            );

            CREATE INDEX IF NOT EXISTS pending_jobs
                ON integration.processing_jobs(tenant_id,available_at)
                WHERE status='pending';

            CREATE INDEX IF NOT EXISTS expired_jobs
                ON integration.processing_jobs(lease_until)
                WHERE status='running';

            ALTER TABLE integration.processing_jobs ENABLE ROW LEVEL SECURITY;
            ALTER TABLE integration.processing_jobs FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON integration.processing_jobs;
            CREATE POLICY tenant_isolation ON integration.processing_jobs
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS integration.processing_jobs CASCADE;
            SQL);
    }
};
