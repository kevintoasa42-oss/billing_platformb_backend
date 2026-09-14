<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound append-only processing attempt records. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS integration.processing_attempts(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                job_id uuid NOT NULL,
                fencing_token bigint NOT NULL,
                outcome text NOT NULL,
                created_at timestamptz NOT NULL DEFAULT now(),
                PRIMARY KEY(tenant_id,id),
                FOREIGN KEY(tenant_id,job_id) REFERENCES integration.processing_jobs(tenant_id,id)
            );

            ALTER TABLE integration.processing_attempts ENABLE ROW LEVEL SECURITY;
            ALTER TABLE integration.processing_attempts FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON integration.processing_attempts;
            CREATE POLICY tenant_isolation ON integration.processing_attempts
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS integration.processing_attempts CASCADE;
            SQL);
    }
};
