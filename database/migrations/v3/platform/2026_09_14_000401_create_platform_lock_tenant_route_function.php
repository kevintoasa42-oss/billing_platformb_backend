<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION platform.lock_tenant_route()
            RETURNS SETOF platform.tenant_data_routes
            LANGUAGE sql
            SECURITY DEFINER
            SET search_path = pg_catalog
            AS $$
                SELECT r.* FROM platform.tenant_data_routes r
                WHERE r.tenant_id = auth.tenant_id()
                FOR SHARE
            $$;
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP FUNCTION IF EXISTS platform.lock_tenant_route()');
    }
};
