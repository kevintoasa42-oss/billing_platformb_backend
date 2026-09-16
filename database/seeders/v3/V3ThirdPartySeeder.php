<?php

namespace Database\Seeders\v3;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds the default third party: CONSUMIDOR FINAL.
 *
 * The SRI Ecuador requires every invoice to have a recipient. When the
 * buyer does not provide identification, the invoice is issued to
 * CONSUMIDOR FINAL using the reserved identification 9999999999999
 * (13 digits) with identification_type '07' (CF).
 *
 * This is the ONLY third party seeded by default. Real customers and
 * carriers should be created through the ThirdParty and Carrier APIs.
 *
 * Idempotent: skips if the consumer final already exists by identification.
 */
final class V3ThirdPartySeeder extends Seeder
{
    public function run(): void
    {
        $db = DB::connection('master_v3');

        $tenant = $db->table('platform.tenants')
            ->where('ruc', V3AuthenticationSeeder::TENANT_RUC)
            ->first(['id']);
        if (! $tenant) {
            $this->command->warn('V3ThirdPartySeeder: tenant not found. Run V3AuthenticationSeeder first.');

            return;
        }

        $tenantId = (string) $tenant->id;

        // SRI reserved identification for CONSUMIDOR FINAL.
        $identification = '9999999999999';
        $identificationType = '07';

        $db->transaction(function () use ($db, $tenantId, $identification, $identificationType): void {
            $db->select("SELECT set_config('app.tenant_id', ?, true)", [$tenantId]);

            $exists = $db->table('core.third_parties')
                ->where('tenant_id', $tenantId)
                ->where('identification', $identification)
                ->exists();

            if ($exists) {
                $this->command->info("V3ThirdPartySeeder: CONSUMIDOR FINAL already exists, skipping.");

                return;
            }

            $thirdPartyId = (string) Str::uuid();

            $db->table('core.third_parties')->insert([
                'id' => $thirdPartyId,
                'tenant_id' => $tenantId,
                'name' => 'CONSUMIDOR FINAL',
                'identification' => $identification,
                'identification_type' => $identificationType,
                'person_type' => 'natural',
                'must_invoice' => false,
            ]);

            // Assign the 'customer' role so the consumer final appears in
            // customer listings and can be used as an invoice recipient.
            $db->table('core.third_party_roles')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'third_party_id' => $thirdPartyId,
                'role' => 'customer',
            ]);
        });
    }
}
