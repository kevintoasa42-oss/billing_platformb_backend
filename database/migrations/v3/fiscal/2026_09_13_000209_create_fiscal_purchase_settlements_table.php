<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Purchase settlement subtype. RLS enabled.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS fiscal.purchase_settlements(
                tenant_id uuid NOT NULL,
                id uuid NOT NULL,
                eligibility_id uuid NOT NULL,
                PRIMARY KEY(tenant_id,id),
                FOREIGN KEY(tenant_id,id) REFERENCES fiscal.documents(tenant_id,id),
                FOREIGN KEY(tenant_id,eligibility_id) REFERENCES fiscal.purchase_settlement_eligibility(tenant_id,id)
            );

            ALTER TABLE fiscal.purchase_settlements ENABLE ROW LEVEL SECURITY;
            ALTER TABLE fiscal.purchase_settlements FORCE ROW LEVEL SECURITY;
            DROP POLICY IF EXISTS tenant_isolation ON fiscal.purchase_settlements;
            CREATE POLICY tenant_isolation ON fiscal.purchase_settlements
                USING (tenant_id = auth.tenant_id())
                WITH CHECK (tenant_id = auth.tenant_id());
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP TABLE IF EXISTS fiscal.purchase_settlements CASCADE;
            SQL);
    }
};
