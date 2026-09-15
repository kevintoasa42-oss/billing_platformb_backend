<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Platform subscription invoices, isolated by tenant in the V3 master database. */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS platform.invoices(
                tenant_id uuid NOT NULL REFERENCES platform.tenants(id),
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                legacy_id bigint GENERATED ALWAYS AS IDENTITY,
                contract_id uuid,
                invoice_number text NOT NULL,
                status text NOT NULL DEFAULT 'draft',
                issue_date date NOT NULL DEFAULT current_date,
                due_date date,
                subtotal numeric(18,2) NOT NULL DEFAULT 0 CHECK(subtotal >= 0),
                tax numeric(18,2) NOT NULL DEFAULT 0 CHECK(tax >= 0),
                total numeric(18,2) NOT NULL DEFAULT 0 CHECK(total = subtotal + tax),
                currency char(3) NOT NULL DEFAULT 'USD',
                paid_at timestamptz,
                notes text,
                created_at timestamptz NOT NULL DEFAULT now(),
                updated_at timestamptz NOT NULL DEFAULT now(),
                PRIMARY KEY(tenant_id, id),
                UNIQUE(legacy_id),
                UNIQUE(tenant_id, invoice_number)
            );

            CREATE INDEX IF NOT EXISTS platform_invoices_tenant_issue_date
                ON platform.invoices(tenant_id, issue_date DESC, legacy_id DESC);

            ALTER TABLE platform.invoices ENABLE ROW LEVEL SECURITY;
            ALTER TABLE platform.invoices FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON platform.invoices;
            CREATE POLICY tenant_isolation ON platform.invoices
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS platform.invoices CASCADE;
            SQL);
    }
};
