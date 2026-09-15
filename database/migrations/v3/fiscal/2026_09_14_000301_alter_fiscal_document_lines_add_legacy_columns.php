<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add legacy_id (bigint IDENTITY) and monetary columns to fiscal.document_lines
 * for compatibility with the ConsolidatedWebController invoice surface.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            ALTER TABLE fiscal.document_lines
                ADD COLUMN IF NOT EXISTS legacy_id bigint GENERATED ALWAYS AS IDENTITY,
                ADD COLUMN IF NOT EXISTS discount numeric(18,2) NOT NULL DEFAULT 0 CHECK(discount>=0),
                ADD COLUMN IF NOT EXISTS tax numeric(18,2) NOT NULL DEFAULT 0 CHECK(tax>=0),
                ADD COLUMN IF NOT EXISTS total numeric(18,2) NOT NULL DEFAULT 0 CHECK(total>=0),
                ADD COLUMN IF NOT EXISTS taxable_base numeric(18,2),
                ADD COLUMN IF NOT EXISTS sri_principal_code text;

            CREATE UNIQUE INDEX IF NOT EXISTS document_lines_legacy_id_unique
                ON fiscal.document_lines (legacy_id);
        SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP INDEX IF EXISTS fiscal.document_lines_legacy_id_unique;
            ALTER TABLE fiscal.document_lines
                DROP COLUMN IF EXISTS legacy_id,
                DROP COLUMN IF EXISTS discount,
                DROP COLUMN IF EXISTS tax,
                DROP COLUMN IF EXISTS total,
                DROP COLUMN IF EXISTS taxable_base,
                DROP COLUMN IF EXISTS sri_principal_code;
        SQL);
    }
};
