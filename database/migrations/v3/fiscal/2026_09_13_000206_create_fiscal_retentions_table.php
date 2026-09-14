<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * SRI retention subtype (code 07). RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS fiscal.retentions(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL,
                metadata jsonb NOT NULL DEFAULT '{}'::jsonb,
                PRIMARY KEY(tenant_id,id),
                FOREIGN KEY(tenant_id,id) REFERENCES fiscal.documents(tenant_id,id)
            );

            ALTER TABLE fiscal.retentions ENABLE ROW LEVEL SECURITY;
            ALTER TABLE fiscal.retentions FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON fiscal.retentions;
            CREATE POLICY tenant_isolation ON fiscal.retentions
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS fiscal.retentions CASCADE;
            SQL);
    }
};
