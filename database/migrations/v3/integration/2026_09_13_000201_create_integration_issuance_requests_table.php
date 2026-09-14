<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound idempotent issuance requests for document generation. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS integration.issuance_requests(
                tenant_id uuid NOT NULL REFERENCES platform.tenants(id),
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                environment text NOT NULL CHECK(environment='lab'),
                idempotency_key text NOT NULL,
                request_hash text NOT NULL,
                document_id uuid,
                PRIMARY KEY(tenant_id,id),
                UNIQUE(tenant_id,environment,idempotency_key),
                FOREIGN KEY(tenant_id,document_id) REFERENCES fiscal.documents(tenant_id,id)
            );

            ALTER TABLE integration.issuance_requests ENABLE ROW LEVEL SECURITY;
            ALTER TABLE integration.issuance_requests FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON integration.issuance_requests;
            CREATE POLICY tenant_isolation ON integration.issuance_requests
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS integration.issuance_requests CASCADE;
            SQL);
    }
};
