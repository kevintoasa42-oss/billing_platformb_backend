<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound sequential number reservations linked to issuance requests. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS integration.number_reservations(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                sequence_id uuid NOT NULL,
                number bigint NOT NULL,
                issuance_request_id uuid NOT NULL,
                PRIMARY KEY(tenant_id,id),
                FOREIGN KEY(tenant_id,sequence_id) REFERENCES fiscal.sequences(tenant_id,id),
                FOREIGN KEY(tenant_id,issuance_request_id) REFERENCES integration.issuance_requests(tenant_id,id),
                UNIQUE(tenant_id,sequence_id,number),
                UNIQUE(tenant_id,issuance_request_id)
            );

            ALTER TABLE integration.number_reservations ENABLE ROW LEVEL SECURITY;
            ALTER TABLE integration.number_reservations FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON integration.number_reservations;
            CREATE POLICY tenant_isolation ON integration.number_reservations
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS integration.number_reservations CASCADE;
            SQL);
    }
};
