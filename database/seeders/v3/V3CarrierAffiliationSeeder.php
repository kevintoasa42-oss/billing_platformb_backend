<?php

namespace Database\Seeders\v3;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds carrier affiliations and vehicle assignments for the demo tenant.
 *
 * Idempotent: skips affiliations that already exist by third_party_id + validity.
 */
final class V3CarrierAffiliationSeeder extends Seeder
{
    public function run(): void
    {
        $db = DB::connection('master_v3');

        $tenant = $db->table('platform.tenants')->where('ruc', '1790000000001')->first(['id']);
        if (! $tenant) {
            $this->command->warn('V3CarrierAffiliationSeeder: tenant not found.');

            return;
        }

        $tenantId = (string) $tenant->id;

        $thirdParty = $db->table('core.third_parties')
            ->where('tenant_id', $tenantId)
            ->where('identification', '1790000000002')
            ->first(['id']);

        if (! $thirdParty) {
            $this->command->warn('V3CarrierAffiliationSeeder: third party not found. Run V3ThirdPartySeeder first.');

            return;
        }

        $vehicle = $db->table('core.vehicles')
            ->where('tenant_id', $tenantId)
            ->where('plate', 'DEF456')
            ->first(['id']);

        if (! $vehicle) {
            $this->command->warn('V3CarrierAffiliationSeeder: vehicle DEF456 not found. Run V3VehicleSeeder first.');

            return;
        }

        $affiliations = [
            ['start' => '2026-01-01', 'end' => '2026-06-30'],
            ['start' => '2026-07-01', 'end' => '2026-12-31'],
        ];

        $db->transaction(function () use ($db, $tenantId, $thirdParty, $vehicle, $affiliations): void {
            $db->select("SELECT set_config('app.tenant_id', ?, true)", [$tenantId]);

            foreach ($affiliations as $aff) {
                $exists = $db->table('core.carrier_affiliations')
                    ->where('tenant_id', $tenantId)
                    ->where('third_party_id', $thirdParty->id)
                    ->whereRaw('validity @> ?::date', [$aff['start']])
                    ->exists();

                if ($exists) {
                    $this->command->info("V3CarrierAffiliationSeeder: affiliation {$aff['start']}/{$aff['end']} already exists, skipping.");

                    continue;
                }

                $affId = (string) Str::uuid();
                $db->table('core.carrier_affiliations')->insert([
                    'id' => $affId,
                    'tenant_id' => $tenantId,
                    'third_party_id' => $thirdParty->id,
                    'validity' => DB::raw("daterange('{$aff['start']}', '{$aff['end']}', '[)')"),
                ]);

                $db->table('core.carrier_vehicle_assignments')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'affiliation_id' => $affId,
                    'vehicle_id' => $vehicle->id,
                    'validity' => DB::raw("daterange('{$aff['start']}', '{$aff['end']}', '[)')"),
                ]);
            }
        });
    }
}
