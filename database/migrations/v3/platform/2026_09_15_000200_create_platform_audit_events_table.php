<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Append-only audit log for third-party field mutations.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS platform.audit_events (
                id bigserial PRIMARY KEY,
                tenant_id uuid NOT NULL,
                actor_id uuid,
                action text NOT NULL,
                entity_type text NOT NULL,
                entity_id uuid,
                before_state jsonb NOT NULL DEFAULT '[]'::jsonb,
                after_state jsonb NOT NULL DEFAULT '[]'::jsonb,
                metadata jsonb NOT NULL DEFAULT '{}'::jsonb,
                created_at timestamptz NOT NULL DEFAULT now()
            );

            CREATE INDEX IF NOT EXISTS platform_audit_events_tenant_id_index
                ON platform.audit_events (tenant_id);

            CREATE INDEX IF NOT EXISTS platform_audit_events_entity_index
                ON platform.audit_events (entity_type, entity_id);

            DROP TRIGGER IF EXISTS platform_audit_events_immutable ON platform.audit_events;
            CREATE TRIGGER platform_audit_events_immutable
                BEFORE UPDATE OR DELETE ON platform.audit_events
                FOR EACH ROW EXECUTE FUNCTION platform.immutable_audit();
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TRIGGER IF EXISTS platform_audit_events_immutable ON platform.audit_events;
            DROP TABLE IF EXISTS platform.audit_events;
            SQL);
    }
};
