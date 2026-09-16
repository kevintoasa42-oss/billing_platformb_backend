<?php

namespace Database\Seeders\v3;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds the tenant-bound Company (one per tenant) with activities.
 *
 * The default demo company uses real SRI-registered data:
 *   RUC:           1752331700001
 *   Razón social:  KEVIN XAVIER TOASA ANRANGO
 *   Dirección:     Caupicho
 *   Actividad:     494110 (Transporte de carga por carretera — cooperativas)
 *   Régimen:       RIMPE (inicio de actividades 2026-08-25)
 *
 * Idempotent: skips if a company already exists for the tenant.
 */
final class V3CompanySeeder extends Seeder
{
    public function run(): void
    {
        $db = DB::connection('master_v3');

        $tenant = $db->table('platform.tenants')
            ->where('ruc', V3AuthenticationSeeder::TENANT_RUC)
            ->first(['id']);
        if (! $tenant) {
            $this->command->warn('V3CompanySeeder: tenant not found. Run V3AuthenticationSeeder first.');

            return;
        }

        $tenantId = (string) $tenant->id;

        $existing = $db->table('core.companies')->where('tenant_id', $tenantId)->first(['id']);
        if ($existing) {
            $this->command->info('V3CompanySeeder: company already exists, skipping.');

            return;
        }

        $companyId = (string) Str::uuid();

        $db->transaction(function () use ($db, $tenantId, $companyId): void {
            $db->select("SELECT set_config('app.tenant_id', ?, true)", [$tenantId]);

            $db->table('core.companies')->insert([
                'id' => $companyId,
                'tenant_id' => $tenantId,
                'name' => 'KEVIN XAVIER TOASA ANRANGO',
                'ruc' => V3AuthenticationSeeder::TENANT_RUC,
                'legal_name' => 'KEVIN XAVIER TOASA ANRANGO',
                'trade_name' => 'KEVIN XAVIER TOASA ANRANGO',
                'matrix_address' => 'Caupicho',
                'operations_start_date' => '2026-08-25',
                'city_id' => null,
                'phone' => '0998813666',
                'corporate_email' => 'kevintoasa43@gmail.com',
            ]);

            // Assign activities (real CIIU codes: 494110 transporte de carga
            // por carretera — cooperativas, 522510 paquetería y mensajería).
            foreach (['494110', '522510'] as $index => $activityId) {
                $db->table('core.company_activities')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'activity_id' => $activityId,
                    'validity' => $db->raw("daterange(CURRENT_DATE, NULL, '[)')"),
                    'is_primary' => $index === 0,
                ]);
            }
        });
    }
}
