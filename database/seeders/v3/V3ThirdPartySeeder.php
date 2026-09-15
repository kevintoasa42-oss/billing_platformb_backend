<?php

namespace Database\Seeders\v3;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds third parties (clientes/proveedores) for the demo tenant.
 *
 * Idempotent: skips third parties that already exist by identification.
 */
final class V3ThirdPartySeeder extends Seeder
{
    public function run(): void
    {
        $db = DB::connection('master_v3');

        $tenant = $db->table('platform.tenants')->where('ruc', '1790000000001')->first(['id']);
        if (! $tenant) {
            $this->command->warn('V3ThirdPartySeeder: tenant not found. Run V3AuthenticationSeeder first.');

            return;
        }

        $tenantId = (string) $tenant->id;

        $thirdParties = [
            ['name' => 'Transportes Demo Cia.', 'identification' => '1790000000002', 'identification_type' => '04', 'person_type' => 'juridical'],
            ['name' => 'Cliente Demo Uno', 'identification' => '1712345678', 'identification_type' => '05', 'person_type' => 'natural'],
            ['name' => 'Cliente Demo Dos', 'identification' => '1798765432', 'identification_type' => '04', 'person_type' => 'juridical'],
        ];

        $db->transaction(function () use ($db, $tenantId, $thirdParties): void {
            $db->select("SELECT set_config('app.tenant_id', ?, true)", [$tenantId]);

            foreach ($thirdParties as $tp) {
                $exists = $db->table('core.third_parties')
                    ->where('tenant_id', $tenantId)
                    ->where('identification', $tp['identification'])
                    ->exists();

                if ($exists) {
                    $this->command->info("V3ThirdPartySeeder: third party identification={$tp['identification']} already exists, skipping.");

                    continue;
                }

                $db->table('core.third_parties')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'name' => $tp['name'],
                    'identification' => $tp['identification'],
                    'identification_type' => $tp['identification_type'],
                    'person_type' => $tp['person_type'],
                    'must_invoice' => true,
                ]);
            }
        });
    }
}
