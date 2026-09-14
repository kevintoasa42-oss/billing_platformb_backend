<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound append-only document event log. Protected by immutable_audit trigger. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS fiscal.document_events(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                document_id uuid NOT NULL,
                event text NOT NULL,
                created_at timestamptz NOT NULL DEFAULT now(),
                PRIMARY KEY(tenant_id,id),
                FOREIGN KEY(tenant_id,document_id) REFERENCES fiscal.documents(tenant_id,id)
            );

            DROP TRIGGER IF EXISTS immutable_document_events ON fiscal.document_events;
            CREATE TRIGGER immutable_document_events
                BEFORE UPDATE OR DELETE ON fiscal.document_events
                FOR EACH ROW EXECUTE FUNCTION platform.immutable_audit();

            ALTER TABLE fiscal.document_events ENABLE ROW LEVEL SECURITY;
            ALTER TABLE fiscal.document_events FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON fiscal.document_events;
            CREATE POLICY tenant_isolation ON fiscal.document_events
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS fiscal.document_events CASCADE;
            SQL);
    }
};
