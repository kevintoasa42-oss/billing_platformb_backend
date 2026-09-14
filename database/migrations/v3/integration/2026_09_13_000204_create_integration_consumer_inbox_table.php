<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-bound consumer inbox for idempotent message processing. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS integration.consumer_inbox(
                tenant_id uuid NOT NULL REFERENCES platform.tenants(id),
                id uuid NOT NULL DEFAULT gen_random_uuid(),
                consumer_name text NOT NULL,
                message_id uuid NOT NULL,
                received_at timestamptz NOT NULL DEFAULT now(),
                PRIMARY KEY(tenant_id,id),
                UNIQUE(consumer_name,message_id)
            );

            ALTER TABLE integration.consumer_inbox ENABLE ROW LEVEL SECURITY;
            ALTER TABLE integration.consumer_inbox FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON integration.consumer_inbox;
            CREATE POLICY tenant_isolation ON integration.consumer_inbox
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS integration.consumer_inbox CASCADE;
            SQL);
    }
};
