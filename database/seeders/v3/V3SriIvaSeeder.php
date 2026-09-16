<?php

namespace Database\Seeders\v3;

use Database\Seeders\v3\V3AuthenticationSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds tenant-bound SRI IVA types and percentages.
 *
 * Uses the official SRI Ecuador catalog (Tabla 4 - Tarifas de IVA):
 *   0 = IVA 0%      (tarifa cero, Art. 55/56 LRTI)
 *   2 = IVA 12%     (tarifa histórica, vigente hasta 2023-12-31)
 *   3 = IVA 14%     (tarifa temporal 2020-2023)
 *   4 = IVA 15%     (tarifa general vigente desde 2024-01-01, Ley para
 *                    Enfrentar el Conflicto Armado Interno)
 *   6 = No objeto de IVA
 *   7 = Exento de IVA
 *
 * Idempotent: skips types that already exist by sri_code.
 */
final class V3SriIvaSeeder extends Seeder
{
    public function run(): void
    {
        $db = DB::connection('master_v3');

        $tenant = $db->table('platform.tenants')->where("ruc", V3AuthenticationSeeder::TENANT_RUC)->first(['id']);
        if (! $tenant) {
            $this->command->warn('V3SriIvaSeeder: tenant not found. Run V3AuthenticationSeeder first.');

            return;
        }

        $tenantId = (string) $tenant->id;

        // Official SRI IVA types (Tabla 4). sri_code is the normative code
        // used in the XML; percentage tracks the current numeric rate.
        $types = [
            ['name' => 'IVA 0%', 'percentage' => 0, 'sri_code' => '0'],
            ['name' => 'IVA 12%', 'percentage' => 12, 'sri_code' => '2'],
            ['name' => 'IVA 14%', 'percentage' => 14, 'sri_code' => '3'],
            ['name' => 'IVA 15%', 'percentage' => 15, 'sri_code' => '4'],
            ['name' => 'No objeto de IVA', 'percentage' => 0, 'sri_code' => '6'],
            ['name' => 'Exento de IVA', 'percentage' => 0, 'sri_code' => '7'],
        ];

        // Historical percentage rows per SRI type. The SRI only keeps one
        // active percentage per type at a time; we seed the full history so
        // historical invoices reproduce the rate that was in effect.
        // `code` is the SRI percentage code (Tabla 4) used in the XML.
        $percentages = [
            '0' => [['0.00', '2000-01-01', null, '0']],
            '2' => [['12.00', '2000-01-01', '2023-12-31', '2'], ['15.00', '2024-01-01', null, '4']],
            '3' => [['14.00', '2020-01-01', '2023-12-31', '3']],
            '4' => [['15.00', '2024-01-01', null, '4']],
            '6' => [['0.00', '2000-01-01', null, '6']],
            '7' => [['0.00', '2000-01-01', null, '7']],
        ];

        $db->transaction(function () use ($db, $tenantId, $types, $percentages): void {
            $db->select("SELECT set_config('app.tenant_id', ?, true)", [$tenantId]);

            foreach ($types as $type) {
                $existing = $db->table('core.sri_iva_types')
                    ->where('tenant_id', $tenantId)
                    ->where('sri_code', $type['sri_code'])
                    ->first(['id']);

                if ($existing) {
                    $typeId = (string) $existing->id;
                    $this->command->info("V3SriIvaSeeder: type sri_code={$type['sri_code']} already exists, skipping.");
                } else {
                    $typeId = $db->table('core.sri_iva_types')->insertGetId([
                        'tenant_id' => $tenantId,
                        'name' => $type['name'],
                        'percentage' => $type['percentage'],
                        'sri_code' => $type['sri_code'],
                        'is_active' => true,
                    ]);
                }

                // Seed historical percentage rows for this type.
                foreach ($percentages[$type['sri_code']] as [$pct, $start, $end, $pctCode]) {
                    $pctExists = $db->table('core.sri_iva_percentages')
                        ->where('tenant_id', $tenantId)
                        ->where('sri_iva_type_id', $typeId)
                        ->where('start_date', $start)
                        ->exists();

                    if ($pctExists) {
                        continue;
                    }

                    $db->table('core.sri_iva_percentages')->insert([
                        'tenant_id' => $tenantId,
                        'sri_iva_type_id' => $typeId,
                        'percentage' => $pct,
                        'start_date' => $start,
                        'end_date' => $end,
                        'code' => $pctCode,
                        'is_active' => true,
                    ]);
                }
            }
        });
    }
}
