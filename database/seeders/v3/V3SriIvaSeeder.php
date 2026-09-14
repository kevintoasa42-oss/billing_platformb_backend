<?php

namespace Database\Seeders\v3;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds tenant-bound SRI IVA types and percentages.
 *
 * Idempotent: skips types that already exist by sri_code.
 */
final class V3SriIvaSeeder extends Seeder
{
    public function run(): void
    {
        $db = DB::connection('master_v3');

        $tenant = $db->table('platform.tenants')->where('ruc', '1790000000001')->first(['id']);
        if (! $tenant) {
            $this->command->warn('V3SriIvaSeeder: tenant not found. Run V3AuthenticationSeeder first.');

            return;
        }

        $tenantId = (string) $tenant->id;

        $types = [
            ['name' => 'IVA 15%', 'percentage' => 15, 'sri_code' => 'IVA15', 'pct_code' => 'P15', 'start_date' => '2026-01-01'],
            ['name' => 'IVA 12%', 'percentage' => 12, 'sri_code' => 'IVA12', 'pct_code' => 'P12', 'start_date' => '2026-01-01'],
            ['name' => 'IVA 0%', 'percentage' => 0, 'sri_code' => 'IVA0', 'pct_code' => 'P0', 'start_date' => '2026-01-01'],
            ['name' => 'No aplica IVA', 'percentage' => 0, 'sri_code' => 'NAIVA', 'pct_code' => 'NA', 'start_date' => '2026-01-01'],
        ];

        $db->transaction(function () use ($db, $tenantId, $types): void {
            $db->select("SELECT set_config('app.tenant_id', ?, true)", [$tenantId]);

            foreach ($types as $type) {
                $exists = $db->table('core.sri_iva_types')
                    ->where('tenant_id', $tenantId)
                    ->where('sri_code', $type['sri_code'])
                    ->exists();

                if ($exists) {
                    $this->command->info("V3SriIvaSeeder: type sri_code={$type['sri_code']} already exists, skipping.");

                    continue;
                }

                $typeId = $db->table('core.sri_iva_types')->insertGetId([
                    'tenant_id' => $tenantId,
                    'name' => $type['name'],
                    'percentage' => $type['percentage'],
                    'sri_code' => $type['sri_code'],
                    'is_active' => true,
                ]);

                $db->table('core.sri_iva_percentages')->insert([
                    'tenant_id' => $tenantId,
                    'sri_iva_type_id' => $typeId,
                    'percentage' => $type['percentage'],
                    'start_date' => $type['start_date'],
                    'end_date' => null,
                    'code' => $type['pct_code'],
                    'is_active' => true,
                ]);
            }
        });
    }
}
