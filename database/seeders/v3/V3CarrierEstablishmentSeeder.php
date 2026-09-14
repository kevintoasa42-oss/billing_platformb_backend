<?php

namespace Database\Seeders\v3;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds carrier establishments and their emission points for the demo tenant.
 *
 * Idempotent: skips by sri_code.
 */
final class V3CarrierEstablishmentSeeder extends Seeder
{
    public function run(): void
    {
        $db = DB::connection('master_v3');

        $tenant = $db->table('platform.tenants')->where('ruc', '1790000000001')->first(['id']);
        if (! $tenant) {
            $this->command->warn('V3CarrierEstablishmentSeeder: tenant not found.');

            return;
        }

        $tenantId = (string) $tenant->id;

        $carrierCompany = $db->table('core.carrier_companies')
            ->where('tenant_id', $tenantId)
            ->first(['id']);

        if (! $carrierCompany) {
            $this->command->warn('V3CarrierEstablishmentSeeder: carrier company not found. Run V3CarrierCompanySeeder first.');

            return;
        }

        $establishments = [
            ['sri_code' => '001', 'name' => 'Matriz Carrier', 'address' => 'Av. Carrier N1'],
            ['sri_code' => '002', 'name' => 'Sucursal Carrier 1', 'address' => 'Av. Carrier N2'],
        ];

        $db->transaction(function () use ($db, $tenantId, $carrierCompany, $establishments): void {
            $db->select("SELECT set_config('app.tenant_id', ?, true)", [$tenantId]);

            foreach ($establishments as $est) {
                $exists = $db->table('core.carrier_establishments')
                    ->where('tenant_id', $tenantId)
                    ->where('carrier_company_id', $carrierCompany->id)
                    ->where('sri_code', $est['sri_code'])
                    ->exists();

                if ($exists) {
                    $this->command->info("V3CarrierEstablishmentSeeder: establishment sri_code={$est['sri_code']} already exists, skipping.");

                    continue;
                }

                $estId = (string) Str::uuid();
                $db->table('core.carrier_establishments')->insert([
                    'id' => $estId,
                    'tenant_id' => $tenantId,
                    'carrier_company_id' => $carrierCompany->id,
                    'sri_code' => $est['sri_code'],
                    'name' => $est['name'],
                    'address' => $est['address'],
                    'is_active' => true,
                ]);

                // Create one emission point per establishment
                $epExists = $db->table('core.carrier_emission_points')
                    ->where('tenant_id', $tenantId)
                    ->where('establishment_id', $estId)
                    ->where('sri_code', '001')
                    ->exists();

                if (! $epExists) {
                    $db->table('core.carrier_emission_points')->insert([
                        'id' => (string) Str::uuid(),
                        'tenant_id' => $tenantId,
                        'establishment_id' => $estId,
                        'sri_code' => '001',
                        'name' => 'Punto de Emisión 001',
                        'next_sequential' => 1,
                        'is_active' => true,
                    ]);
                }
            }
        });
    }
}
