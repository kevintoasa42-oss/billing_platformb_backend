<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound document line items with product snapshot and credit-note lineage. Consolidated with original_line_id from 020. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS fiscal.document_lines(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                document_id uuid NOT NULL,
                product_id uuid NOT NULL,
                description_snapshot text NOT NULL,
                quantity numeric(18,6) NOT NULL CHECK(quantity>0),
                unit_price numeric(18,6) NOT NULL CHECK(unit_price>=0),
                subtotal numeric(18,2) NOT NULL CHECK(subtotal>=0),
                original_line_id uuid,
                PRIMARY KEY(tenant_id,id),
                FOREIGN KEY(tenant_id,document_id) REFERENCES fiscal.documents(tenant_id,id),
                FOREIGN KEY(tenant_id,product_id) REFERENCES core.products(tenant_id,id),
                FOREIGN KEY(tenant_id,original_line_id) REFERENCES fiscal.document_lines(tenant_id,id)
            );

            CREATE INDEX IF NOT EXISTS fiscal_document_lines_original
                ON fiscal.document_lines(tenant_id,original_line_id)
                WHERE original_line_id IS NOT NULL;

            ALTER TABLE fiscal.document_lines ENABLE ROW LEVEL SECURITY;
            ALTER TABLE fiscal.document_lines FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON fiscal.document_lines;
            CREATE POLICY tenant_isolation ON fiscal.document_lines
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS fiscal.document_lines CASCADE;
            SQL);
    }
};
