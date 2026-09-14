<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound fiscal documents (invoices, credit notes, debit notes, retentions, delivery notes, purchase settlements). Consolidated with discount (020), voided status (020), and expanded document types (026). RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS fiscal.documents(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                environment text NOT NULL DEFAULT 'lab' CHECK(environment='lab'),
                document_type text NOT NULL CHECK(document_type IN ('invoice','purchase_settlement','credit_note','debit_note','retention','delivery_note')),
                emission_point_id uuid NOT NULL,
                establishment_code text NOT NULL,
                emission_point_code text NOT NULL,
                sequential bigint NOT NULL CHECK(sequential BETWEEN 1 AND 999999999),
                access_key text,
                authorization_number text,
                issued_at timestamptz NOT NULL DEFAULT now(),
                issuer_snapshot jsonb NOT NULL,
                recipient_snapshot jsonb NOT NULL,
                subtotal numeric(18,2) NOT NULL CHECK(subtotal>=0),
                tax numeric(18,2) NOT NULL CHECK(tax>=0),
                discount numeric(18,2) NOT NULL DEFAULT 0 CHECK(discount>=0),
                total numeric(18,2) NOT NULL CHECK(total = subtotal + tax - discount),
                fiscal_status text NOT NULL DEFAULT 'draft' CHECK(fiscal_status IN ('draft','simulated','imported','submitting','received','authorized','rejected','error','voided')),
                collection_status text NOT NULL DEFAULT 'unpaid',
                delivery_status text NOT NULL DEFAULT 'pending',
                due_at date,
                operation_id uuid NOT NULL,
                carrier_id uuid,
                vehicle_id uuid,
                plate_snapshot text,
                writer_epoch bigint NOT NULL,
                PRIMARY KEY(tenant_id,id),
                FOREIGN KEY(tenant_id,emission_point_id) REFERENCES core.emission_points(tenant_id,id),
                FOREIGN KEY(tenant_id,carrier_id) REFERENCES core.third_parties(tenant_id,id),
                FOREIGN KEY(tenant_id,vehicle_id) REFERENCES core.vehicles(tenant_id,id),
                UNIQUE(tenant_id,environment,document_type,establishment_code,emission_point_code,sequential)
            );

            CREATE UNIQUE INDEX IF NOT EXISTS document_access_key
                ON fiscal.documents(tenant_id,environment,access_key)
                WHERE access_key IS NOT NULL;

            CREATE INDEX IF NOT EXISTS document_list
                ON fiscal.documents(tenant_id,issued_at DESC,id);

            CREATE INDEX IF NOT EXISTS document_state
                ON fiscal.documents(tenant_id,fiscal_status,issued_at DESC);

            ALTER TABLE fiscal.documents ENABLE ROW LEVEL SECURITY;
            ALTER TABLE fiscal.documents FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON fiscal.documents;
            CREATE POLICY tenant_isolation ON fiscal.documents
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS fiscal.documents CASCADE;
            SQL);
    }
};
