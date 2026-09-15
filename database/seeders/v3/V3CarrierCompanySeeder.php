<?php

namespace Database\Seeders\v3;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds carrier companies for the demo tenant.
 *
 * Idempotent: skips carrier companies that already exist by third_party_id.
 */
final class V3CarrierCompanySeeder extends Seeder
{
    public function run(): void
    {
        $db = DB::connection('master_v3');

        $tenant = $db->table('platform.tenants')->where('ruc', '1790000000001')->first(['id']);
        if (! $tenant) {
            $this->command->warn('V3CarrierCompanySeeder: tenant not found. Run V3AuthenticationSeeder first.');

            return;
        }

        $tenantId = (string) $tenant->id;

        $thirdParty = $db->table('core.third_parties')
            ->where('tenant_id', $tenantId)
            ->where('identification', '1790000000002')
            ->first(['id']);

        if (! $thirdParty) {
            $this->command->warn('V3CarrierCompanySeeder: third party 1790000000002 not found. Run V3ThirdPartySeeder first.');

            return;
        }

        $db->transaction(function () use ($db, $tenantId, $thirdParty): void {
            $db->select("SELECT set_config('app.tenant_id', ?, true)", [$tenantId]);

            $exists = $db->table('core.carrier_companies')
                ->where('tenant_id', $tenantId)
                ->where('third_party_id', $thirdParty->id)
                ->exists();

            if ($exists) {
                $this->command->info('V3CarrierCompanySeeder: carrier company already exists, skipping.');

                return;
            }

            $db->table('core.carrier_companies')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'third_party_id' => $thirdParty->id,
                'legal_name' => 'Transportes Demo Cia. S.A.',
                'trade_name' => 'TransDemo',
                'is_active' => true,
            ]);
        });
    }
}
