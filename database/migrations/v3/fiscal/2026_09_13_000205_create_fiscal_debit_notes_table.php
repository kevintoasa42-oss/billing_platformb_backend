<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Debit note subtype. Mirrors credit_notes structure. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS fiscal.debit_notes(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL,
                original_document_id uuid NOT NULL,
                reason text NOT NULL,
                credit_note_action text NOT NULL DEFAULT 'devolucionItem' CHECK(credit_note_action IN ('devolucionItem','descuentoItem','descuentoGeneral')),
                settlement_impact text NOT NULL DEFAULT 'not_applicable' CHECK(settlement_impact IN ('not_applicable','reduce_customer_operation','requires_regularization')),
                settlement_review_status text NOT NULL DEFAULT 'not_required' CHECK(settlement_review_status IN ('not_required','pending','cleared','blocked')),
                PRIMARY KEY(tenant_id,id),
                FOREIGN KEY(tenant_id,id) REFERENCES fiscal.documents(tenant_id,id),
                FOREIGN KEY(tenant_id,original_document_id) REFERENCES fiscal.documents(tenant_id,id)
            );

            ALTER TABLE fiscal.debit_notes ENABLE ROW LEVEL SECURITY;
            ALTER TABLE fiscal.debit_notes FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON fiscal.debit_notes;
            CREATE POLICY tenant_isolation ON fiscal.debit_notes
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS fiscal.debit_notes CASCADE;
            SQL);
    }
};
