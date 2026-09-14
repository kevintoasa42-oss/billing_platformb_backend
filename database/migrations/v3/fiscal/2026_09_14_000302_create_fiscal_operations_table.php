<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Create fiscal.operations table — tracks invoice operations (issue, authorize, void).
 * RLS enabled for tenant isolation.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS fiscal.operations (
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                document_id uuid NOT NULL,
                actor_id uuid,
                action text NOT NULL CHECK(action IN ('issue','authorize','void','submit')),
                status text NOT NULL DEFAULT 'pending' CHECK(status IN ('pending','completed','failed')),
                idempotency_key text,
                created_at timestamptz NOT NULL DEFAULT now(),
                updated_at timestamptz NOT NULL DEFAULT now(),
                PRIMARY KEY (tenant_id, id)
            );

            CREATE INDEX IF NOT EXISTS operations_document_id_index
                ON fiscal.operations (tenant_id, document_id);

            CREATE UNIQUE INDEX IF NOT EXISTS operations_idempotency_key_unique
                ON fiscal.operations (tenant_id, idempotency_key)
                WHERE idempotency_key IS NOT NULL;

            ALTER TABLE fiscal.operations ENABLE ROW LEVEL SECURITY;
            ALTER TABLE fiscal.operations FORCE ROW LEVEL SECURITY;

            CREATE POLICY operations_tenant_isolation ON fiscal.operations
                USING (tenant_id = current_setting('app.tenant_id', true)::uuid);
        SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared("DROP TABLE IF EXISTS fiscal.operations;");
    }
};
