<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound document sequential counters per emission point and document type. RLS enabled. Includes document types from migration 026 (retention, delivery_note) and environment expansion (staging).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS fiscal.sequences(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                environment text NOT NULL CHECK(environment IN ('lab','staging')),
                emission_point_id uuid NOT NULL,
                document_type text NOT NULL CHECK(document_type IN ('invoice','purchase_settlement','credit_note','debit_note','retention','delivery_note')),
                last_number bigint NOT NULL DEFAULT 0 CHECK(last_number BETWEEN 0 AND 999999999),
                PRIMARY KEY(tenant_id,id),
                FOREIGN KEY(tenant_id,emission_point_id) REFERENCES core.emission_points(tenant_id,id),
                UNIQUE(tenant_id,environment,emission_point_id,document_type)
            );

            ALTER TABLE fiscal.sequences ENABLE ROW LEVEL SECURITY;
            ALTER TABLE fiscal.sequences FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON fiscal.sequences;
            CREATE POLICY tenant_isolation ON fiscal.sequences
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS fiscal.sequences CASCADE;
            SQL);
    }
};
