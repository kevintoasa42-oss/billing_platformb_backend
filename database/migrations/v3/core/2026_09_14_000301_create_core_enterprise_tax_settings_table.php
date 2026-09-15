<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.enterprise_tax_settings (
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
                company_id uuid NOT NULL,
                has_accounting boolean NOT NULL DEFAULT false,
                has_inventory boolean NOT NULL DEFAULT false,
                authorization_mode text NOT NULL DEFAULT 'celcer',
                created_at timestamptz NOT NULL DEFAULT now(),
                updated_at timestamptz NOT NULL DEFAULT now(),
                UNIQUE (tenant_id, company_id)
            );
        SQL);

        DB::statement('ALTER TABLE core.enterprise_tax_settings ENABLE ROW LEVEL SECURITY');
        DB::statement(<<<'SQL'
            CREATE POLICY tenant_isolation ON core.enterprise_tax_settings
            USING (tenant_id = auth.tenant_id())
            WITH CHECK (tenant_id = auth.tenant_id());
        SQL);
        DB::statement('ALTER TABLE core.enterprise_tax_settings FORCE ROW LEVEL SECURITY');
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS core.enterprise_tax_settings');
    }
};
