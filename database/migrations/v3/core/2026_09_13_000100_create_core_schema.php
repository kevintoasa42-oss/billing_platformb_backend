<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Creates the core schema and the btree_gist extension needed for EXCLUDE constraints.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE EXTENSION IF NOT EXISTS btree_gist;
            CREATE SCHEMA IF NOT EXISTS core;
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP SCHEMA IF EXISTS core CASCADE;
            SQL);
    }
};
