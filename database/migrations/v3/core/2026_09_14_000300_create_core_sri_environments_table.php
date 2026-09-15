<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.sri_environments (
                tenant_id uuid NOT NULL,
                id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                name text NOT NULL,
                code text NOT NULL,
                created_at timestamptz NOT NULL DEFAULT now(),
                updated_at timestamptz NOT NULL DEFAULT now(),
                UNIQUE (tenant_id, code)
            );
        SQL);

        DB::statement('ALTER TABLE core.sri_environments ENABLE ROW LEVEL SECURITY');
        DB::statement(<<<'SQL'
            CREATE POLICY tenant_isolation ON core.sri_environments
            USING (tenant_id = auth.tenant_id())
            WITH CHECK (tenant_id = auth.tenant_id());
        SQL);
        DB::statement('ALTER TABLE core.sri_environments FORCE ROW LEVEL SECURITY');

        $tenants = DB::table('platform.tenants')->pluck('id');
        foreach ($tenants as $tenantId) {
            DB::table('core.sri_environments')->insert([
                ['tenant_id' => $tenantId, 'name' => 'Pruebas', 'code' => '1'],
                ['tenant_id' => $tenantId, 'name' => 'Produccion', 'code' => '2'],
            ]);
        }
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS core.sri_environments');
    }
};
