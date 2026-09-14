<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Invoice subtype table. One row per invoice document. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS fiscal.invoices(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL,
                PRIMARY KEY(tenant_id,id),
                FOREIGN KEY(tenant_id,id) REFERENCES fiscal.documents(tenant_id,id)
            );

            ALTER TABLE fiscal.invoices ENABLE ROW LEVEL SECURITY;
            ALTER TABLE fiscal.invoices FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON fiscal.invoices;
            CREATE POLICY tenant_isolation ON fiscal.invoices
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS fiscal.invoices CASCADE;
            SQL);
    }
};
