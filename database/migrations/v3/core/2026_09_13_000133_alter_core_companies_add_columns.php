<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->statement(<<<'SQL'
            ALTER TABLE core.companies
                ADD COLUMN IF NOT EXISTS legal_name text,
                ADD COLUMN IF NOT EXISTS trade_name text,
                ADD COLUMN IF NOT EXISTS matrix_address text,
                ADD COLUMN IF NOT EXISTS operations_start_date date,
                ADD COLUMN IF NOT EXISTS city_id bigint,
                ADD COLUMN IF NOT EXISTS phone text,
                ADD COLUMN IF NOT EXISTS corporate_email text;
        SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->statement(<<<'SQL'
            ALTER TABLE core.companies
                DROP COLUMN IF EXISTS corporate_email,
                DROP COLUMN IF EXISTS phone,
                DROP COLUMN IF EXISTS city_id,
                DROP COLUMN IF EXISTS operations_start_date,
                DROP COLUMN IF EXISTS matrix_address,
                DROP COLUMN IF EXISTS trade_name,
                DROP COLUMN IF EXISTS legal_name;
        SQL);
    }
};
