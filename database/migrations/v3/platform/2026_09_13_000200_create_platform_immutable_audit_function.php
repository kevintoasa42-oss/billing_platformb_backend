<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Creates the append-only audit trigger function used by fiscal.document_events and platform.audit_events.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION platform.immutable_audit()
            RETURNS trigger
            LANGUAGE plpgsql
            AS $$
            BEGIN
                RAISE EXCEPTION 'audit is append-only';
            END;
            $$;
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP FUNCTION IF EXISTS platform.immutable_audit();
            SQL);
    }
};
