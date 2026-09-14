<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound append-only settlement allocations between customer operations and carrier received documents. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS fiscal.settlement_allocations(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                operation_id uuid NOT NULL,
                customer_document_id uuid,
                received_document_id uuid NOT NULL,
                carrier_company_id uuid NOT NULL,
                amount numeric(18,2) NOT NULL CHECK(amount>0),
                status text NOT NULL DEFAULT 'active' CHECK(status IN ('active','reversed')),
                idempotency_key text NOT NULL,
                created_by uuid,
                reversed_at timestamptz,
                created_at timestamptz NOT NULL DEFAULT now(),
                PRIMARY KEY(tenant_id,id),
                UNIQUE(tenant_id,idempotency_key),
                FOREIGN KEY(tenant_id,operation_id) REFERENCES core.transport_operations(tenant_id,id),
                FOREIGN KEY(tenant_id,customer_document_id) REFERENCES fiscal.documents(tenant_id,id),
                FOREIGN KEY(tenant_id,received_document_id) REFERENCES fiscal.received_documents(tenant_id,id),
                FOREIGN KEY(tenant_id,carrier_company_id) REFERENCES core.carrier_companies(tenant_id,id),
                FOREIGN KEY(created_by) REFERENCES auth.users(id)
            );

            CREATE INDEX IF NOT EXISTS fiscal_settlement_allocations_operation
                ON fiscal.settlement_allocations(tenant_id, operation_id, status);

            CREATE INDEX IF NOT EXISTS fiscal_settlement_allocations_carrier
                ON fiscal.settlement_allocations(tenant_id, carrier_company_id, status, created_at DESC);

            ALTER TABLE fiscal.settlement_allocations ENABLE ROW LEVEL SECURITY;
            ALTER TABLE fiscal.settlement_allocations FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON fiscal.settlement_allocations;
            CREATE POLICY tenant_isolation ON fiscal.settlement_allocations
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS fiscal.settlement_allocations CASCADE;
            SQL);
    }
};
