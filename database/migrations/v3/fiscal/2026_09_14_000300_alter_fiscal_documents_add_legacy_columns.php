<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add legacy_id (bigint IDENTITY) and legacy status columns to fiscal.documents
 * for compatibility with the ConsolidatedWebController invoice surface.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            ALTER TABLE fiscal.documents
                ADD COLUMN IF NOT EXISTS legacy_id bigint GENERATED ALWAYS AS IDENTITY,
                ADD COLUMN IF NOT EXISTS legacy_sri_status text,
                ADD COLUMN IF NOT EXISTS legacy_internal_status text,
                ADD COLUMN IF NOT EXISTS legacy_fiscal_status text,
                ADD COLUMN IF NOT EXISTS legacy_fiscal_provider text,
                ADD COLUMN IF NOT EXISTS source_database text,
                ADD COLUMN IF NOT EXISTS not_valid_for_sri boolean NOT NULL DEFAULT false,
                ADD COLUMN IF NOT EXISTS fiscal_status_summary text;

            CREATE UNIQUE INDEX IF NOT EXISTS documents_legacy_id_unique
                ON fiscal.documents (legacy_id);
        SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP INDEX IF EXISTS fiscal.documents_legacy_id_unique;
            ALTER TABLE fiscal.documents
                DROP COLUMN IF EXISTS legacy_id,
                DROP COLUMN IF EXISTS legacy_sri_status,
                DROP COLUMN IF EXISTS legacy_internal_status,
                DROP COLUMN IF EXISTS legacy_fiscal_status,
                DROP COLUMN IF EXISTS legacy_fiscal_provider,
                DROP COLUMN IF EXISTS source_database,
                DROP COLUMN IF EXISTS not_valid_for_sri,
                DROP COLUMN IF EXISTS fiscal_status_summary;
        SQL);
    }
};
