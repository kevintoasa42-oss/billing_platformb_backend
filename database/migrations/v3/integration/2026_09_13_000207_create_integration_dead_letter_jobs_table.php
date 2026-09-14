<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound dead letter queue for failed processing jobs. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS integration.dead_letter_jobs(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                job_id uuid NOT NULL,
                reason text NOT NULL,
                PRIMARY KEY(tenant_id,id),
                FOREIGN KEY(tenant_id,job_id) REFERENCES integration.processing_jobs(tenant_id,id),
                UNIQUE(tenant_id,job_id)
            );

            ALTER TABLE integration.dead_letter_jobs ENABLE ROW LEVEL SECURITY;
            ALTER TABLE integration.dead_letter_jobs FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON integration.dead_letter_jobs;
            CREATE POLICY tenant_isolation ON integration.dead_letter_jobs
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS integration.dead_letter_jobs CASCADE;
            SQL);
    }
};
