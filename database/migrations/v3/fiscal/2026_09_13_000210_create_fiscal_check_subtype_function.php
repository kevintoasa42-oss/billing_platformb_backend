<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Creates the check_subtype trigger function and constraint triggers on documents and all subtype tables. Ensures exactly one subtype per document. Includes retention and delivery_note from migration 026.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION fiscal.check_subtype()
            RETURNS trigger
            LANGUAGE plpgsql
            AS $$
            DECLARE
                counts int;
                expected_type text;
            BEGIN
                SELECT document_type INTO expected_type
                FROM fiscal.documents
                WHERE tenant_id=NEW.tenant_id AND id=NEW.id;

                SELECT
                    (SELECT count(*) FROM fiscal.invoices WHERE tenant_id=NEW.tenant_id AND id=NEW.id)
                    +(SELECT count(*) FROM fiscal.credit_notes WHERE tenant_id=NEW.tenant_id AND id=NEW.id)
                    +(SELECT count(*) FROM fiscal.debit_notes WHERE tenant_id=NEW.tenant_id AND id=NEW.id)
                    +(SELECT count(*) FROM fiscal.purchase_settlements WHERE tenant_id=NEW.tenant_id AND id=NEW.id)
                    +(SELECT count(*) FROM fiscal.retentions WHERE tenant_id=NEW.tenant_id AND id=NEW.id)
                    +(SELECT count(*) FROM fiscal.delivery_notes WHERE tenant_id=NEW.tenant_id AND id=NEW.id)
                INTO counts;

                IF counts<>1 OR NOT (CASE expected_type
                    WHEN 'invoice' THEN EXISTS(SELECT 1 FROM fiscal.invoices WHERE tenant_id=NEW.tenant_id AND id=NEW.id)
                    WHEN 'credit_note' THEN EXISTS(SELECT 1 FROM fiscal.credit_notes WHERE tenant_id=NEW.tenant_id AND id=NEW.id)
                    WHEN 'debit_note' THEN EXISTS(SELECT 1 FROM fiscal.debit_notes WHERE tenant_id=NEW.tenant_id AND id=NEW.id)
                    WHEN 'purchase_settlement' THEN EXISTS(SELECT 1 FROM fiscal.purchase_settlements WHERE tenant_id=NEW.tenant_id AND id=NEW.id)
                    WHEN 'retention' THEN EXISTS(SELECT 1 FROM fiscal.retentions WHERE tenant_id=NEW.tenant_id AND id=NEW.id)
                    WHEN 'delivery_note' THEN EXISTS(SELECT 1 FROM fiscal.delivery_notes WHERE tenant_id=NEW.tenant_id AND id=NEW.id)
                    ELSE false
                END) THEN
                    RAISE EXCEPTION 'document requires exactly one matching subtype';
                END IF;
                RETURN NEW;
            END;
            $$;

            DROP TRIGGER IF EXISTS document_subtype ON fiscal.documents;
            CREATE CONSTRAINT TRIGGER document_subtype
                AFTER INSERT OR UPDATE ON fiscal.documents
                DEFERRABLE INITIALLY DEFERRED
                FOR EACH ROW EXECUTE FUNCTION fiscal.check_subtype();

            DROP TRIGGER IF EXISTS invoice_subtype ON fiscal.invoices;
            CREATE CONSTRAINT TRIGGER invoice_subtype
                AFTER INSERT OR UPDATE ON fiscal.invoices
                DEFERRABLE INITIALLY DEFERRED
                FOR EACH ROW EXECUTE FUNCTION fiscal.check_subtype();

            DROP TRIGGER IF EXISTS credit_subtype ON fiscal.credit_notes;
            CREATE CONSTRAINT TRIGGER credit_subtype
                AFTER INSERT OR UPDATE ON fiscal.credit_notes
                DEFERRABLE INITIALLY DEFERRED
                FOR EACH ROW EXECUTE FUNCTION fiscal.check_subtype();

            DROP TRIGGER IF EXISTS debit_subtype ON fiscal.debit_notes;
            CREATE CONSTRAINT TRIGGER debit_subtype
                AFTER INSERT OR UPDATE ON fiscal.debit_notes
                DEFERRABLE INITIALLY DEFERRED
                FOR EACH ROW EXECUTE FUNCTION fiscal.check_subtype();

            DROP TRIGGER IF EXISTS settlement_subtype ON fiscal.purchase_settlements;
            CREATE CONSTRAINT TRIGGER settlement_subtype
                AFTER INSERT OR UPDATE ON fiscal.purchase_settlements
                DEFERRABLE INITIALLY DEFERRED
                FOR EACH ROW EXECUTE FUNCTION fiscal.check_subtype();

            DROP TRIGGER IF EXISTS retention_subtype ON fiscal.retentions;
            CREATE CONSTRAINT TRIGGER retention_subtype
                AFTER INSERT OR UPDATE ON fiscal.retentions
                DEFERRABLE INITIALLY DEFERRED
                FOR EACH ROW EXECUTE FUNCTION fiscal.check_subtype();

            DROP TRIGGER IF EXISTS delivery_note_subtype ON fiscal.delivery_notes;
            CREATE CONSTRAINT TRIGGER delivery_note_subtype
                AFTER INSERT OR UPDATE ON fiscal.delivery_notes
                DEFERRABLE INITIALLY DEFERRED
                FOR EACH ROW EXECUTE FUNCTION fiscal.check_subtype();
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP FUNCTION IF EXISTS fiscal.check_subtype() CASCADE;
            SQL);
    }
};
