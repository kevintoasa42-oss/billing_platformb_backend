<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Creates the fiscal schema for electronic invoicing documents, sequences and snapshots.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE SCHEMA IF NOT EXISTS fiscal;
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP SCHEMA IF EXISTS fiscal CASCADE;
            SQL);
    }
};
