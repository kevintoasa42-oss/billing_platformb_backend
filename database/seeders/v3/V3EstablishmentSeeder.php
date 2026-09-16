<?php

namespace Database\Seeders\v3;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds tenant-bound establishments and their emission points.
 *
 * Idempotent: skips establishments that already exist by sri_code.
 */
final class V3EstablishmentSeeder extends Seeder
{
    public function run(): void
    {
        $db = DB::connection('master_v3');

        $tenant = $db->table('platform.tenants')
            ->where('ruc', V3AuthenticationSeeder::TENANT_RUC)
            ->first(['id']);
        if (! $tenant) {
            $this->command->warn('V3EstablishmentSeeder: tenant not found. Run V3AuthenticationSeeder first.');

            return;
        }

        $tenantId = (string) $tenant->id;

        $company = $db->table('core.companies')->where('tenant_id', $tenantId)->first(['id']);
        if (! $company) {
            $this->command->warn('V3EstablishmentSeeder: company not found. Run V3CompanySeeder first.');

            return;
        }

        $companyId = (string) $company->id;

        $establishments = [
            [
                'sri_code' => '001',
                'name' => 'Matriz',
                'legacy_id' => '1',
                'branch_code' => null,
                'address' => 'Caupicho',
                'phone' => '0998813666',
                'email' => 'kevintoasa43@gmail.com',
                'emission_points' => [
                    ['sri_code' => '001', 'name' => 'Punto de Emisión 1', 'legacy_id' => '1', 'is_active' => true, 'is_default' => true, 'has_tax_validity' => true],
                ],
            ],
        ];

        $db->transaction(function () use ($db, $tenantId, $companyId, $establishments): void {
            $db->select("SELECT set_config('app.tenant_id', ?, true)", [$tenantId]);

            foreach ($establishments as $est) {
                $exists = $db->table('core.establishments')
                    ->where('tenant_id', $tenantId)
                    ->where('sri_code', $est['sri_code'])
                    ->exists();

                if ($exists) {
                    $this->command->info("V3EstablishmentSeeder: establishment sri_code={$est['sri_code']} already exists, skipping.");

                    continue;
                }

                $estId = (string) Str::uuid();

                $db->table('core.establishments')->insert([
                    'id' => $estId,
                    'tenant_id' => $tenantId,
                    'company_id' => $companyId,
                    'sri_code' => $est['sri_code'],
                    'name' => $est['name'],
                    'legacy_id' => $est['legacy_id'],
                    'branch_code' => $est['branch_code'],
                    'address' => $est['address'],
                    'phone' => $est['phone'],
                    'email' => $est['email'],
                    'city_id' => null,
                    'is_active' => true,
                ]);

                // Assign activity 494110 (Transporte de carga por carretera).
                $db->table('core.establishment_activities')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'establishment_id' => $estId,
                    'activity_id' => '494110',
                    'validity' => $db->raw("daterange(CURRENT_DATE, NULL, '[)')"),
                ]);

                foreach ($est['emission_points'] as $ep) {
                    $db->table('core.emission_points')->insert([
                        'id' => (string) Str::uuid(),
                        'tenant_id' => $tenantId,
                        'establishment_id' => $estId,
                        'sri_code' => $ep['sri_code'],
                        'name' => $ep['name'],
                        'legacy_id' => $ep['legacy_id'],
                        'is_active' => $ep['is_active'],
                        'is_default' => $ep['is_default'],
                        'has_tax_validity' => $ep['has_tax_validity'],
                    ]);
                }
            }
        });
    }
}
