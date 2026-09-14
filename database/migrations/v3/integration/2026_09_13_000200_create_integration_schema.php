<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Creates the integration schema for issuance requests, processing jobs, outbox/inbox patterns and document artifacts.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE SCHEMA IF NOT EXISTS integration;
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP SCHEMA IF EXISTS integration CASCADE;
            SQL);
    }
};
