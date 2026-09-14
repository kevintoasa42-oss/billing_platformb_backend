<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * SRI delivery note subtype (code 06). RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS fiscal.delivery_notes(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL,
                metadata jsonb NOT NULL DEFAULT '{}'::jsonb,
                PRIMARY KEY(tenant_id,id),
                FOREIGN KEY(tenant_id,id) REFERENCES fiscal.documents(tenant_id,id)
            );

            ALTER TABLE fiscal.delivery_notes ENABLE ROW LEVEL SECURITY;
            ALTER TABLE fiscal.delivery_notes FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON fiscal.delivery_notes;
            CREATE POLICY tenant_isolation ON fiscal.delivery_notes
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS fiscal.delivery_notes CASCADE;
            SQL);
    }
};
