<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Columns required by the V3 third-party creation contract.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            ALTER TABLE core.third_parties
                ADD COLUMN IF NOT EXISTS legacy_id bigint,
                ADD COLUMN IF NOT EXISTS address text,
                ADD COLUMN IF NOT EXISTS phone text,
                ADD COLUMN IF NOT EXISTS email text,
                ADD COLUMN IF NOT EXISTS customer_type_id bigint,
                ADD COLUMN IF NOT EXISTS is_active boolean NOT NULL DEFAULT true;

            CREATE UNIQUE INDEX IF NOT EXISTS core_third_parties_legacy_id_unique
                ON core.third_parties (tenant_id, legacy_id)
                WHERE legacy_id IS NOT NULL;
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP INDEX IF EXISTS core.core_third_parties_legacy_id_unique;

            ALTER TABLE core.third_parties
                DROP COLUMN IF EXISTS legacy_id,
                DROP COLUMN IF EXISTS address,
                DROP COLUMN IF EXISTS phone,
                DROP COLUMN IF EXISTS email,
                DROP COLUMN IF EXISTS customer_type_id,
                DROP COLUMN IF EXISTS is_active;
            SQL);
    }
};
