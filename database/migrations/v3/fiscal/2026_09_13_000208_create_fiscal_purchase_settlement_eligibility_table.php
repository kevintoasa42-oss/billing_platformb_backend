<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound purchase settlement eligibility records. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS fiscal.purchase_settlement_eligibility(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                third_party_id uuid NOT NULL,
                cause text NOT NULL CHECK(cause='synthetic-lab-exception'),
                evidence text NOT NULL,
                validity daterange NOT NULL,
                PRIMARY KEY(tenant_id,id),
                FOREIGN KEY(tenant_id,third_party_id) REFERENCES core.third_parties(tenant_id,id)
            );

            ALTER TABLE fiscal.purchase_settlement_eligibility ENABLE ROW LEVEL SECURITY;
            ALTER TABLE fiscal.purchase_settlement_eligibility FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON fiscal.purchase_settlement_eligibility;
            CREATE POLICY tenant_isolation ON fiscal.purchase_settlement_eligibility
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS fiscal.purchase_settlement_eligibility CASCADE;
            SQL);
    }
};
