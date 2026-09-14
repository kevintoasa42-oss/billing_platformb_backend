<?php

namespace Database\Seeders\v3;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds tenant-bound vehicles.
 *
 * Idempotent: skips vehicles that already exist by plate.
 */
final class V3VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $db = DB::connection('master_v3');

        $tenant = $db->table('platform.tenants')->where('ruc', '1790000000001')->first(['id']);
        if (! $tenant) {
            $this->command->warn('V3VehicleSeeder: tenant not found. Run V3AuthenticationSeeder first.');

            return;
        }

        $tenantId = (string) $tenant->id;

        $vehicles = [
            ['plate' => 'DEF456', 'legacy_id' => '5'],
            ['plate' => 'GHI789', 'legacy_id' => '6'],
            ['plate' => 'JKL012', 'legacy_id' => '7'],
            ['plate' => 'MNO345', 'legacy_id' => '8'],
        ];

        $db->transaction(function () use ($db, $tenantId, $vehicles): void {
            $db->select("SELECT set_config('app.tenant_id', ?, true)", [$tenantId]);

            foreach ($vehicles as $vehicle) {
                $exists = $db->table('core.vehicles')
                    ->where('tenant_id', $tenantId)
                    ->where(function ($q) use ($vehicle): void {
                        $q->where('plate', $vehicle['plate'])
                          ->orWhere('legacy_id', $vehicle['legacy_id']);
                    })
                    ->exists();

                if ($exists) {
                    $this->command->info("V3VehicleSeeder: vehicle plate={$vehicle['plate']} already exists, skipping.");

                    continue;
                }

                $db->table('core.vehicles')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'plate' => $vehicle['plate'],
                    'legacy_id' => $vehicle['legacy_id'],
                ]);
            }
        });
    }
}
