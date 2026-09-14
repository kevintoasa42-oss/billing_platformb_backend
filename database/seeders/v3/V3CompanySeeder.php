<?php

namespace Database\Seeders\v3;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds the tenant-bound Company (one per tenant) with activities.
 *
 * Idempotent: skips if a company already exists for the tenant.
 */
final class V3CompanySeeder extends Seeder
{
    public function run(): void
    {
        $db = DB::connection('master_v3');

        $tenant = $db->table('platform.tenants')->where('ruc', '1790000000001')->first(['id']);
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
                'name' => 'Transportes Demo S.A.',
                'ruc' => '1790000000001',
                'legal_name' => 'Transportes Demo Sociedad Anonima',
                'trade_name' => 'TransDemo',
                'matrix_address' => 'Av. Amazonas N34-451 y Nnuqui',
                'operations_start_date' => '2015-03-12',
                'city_id' => null,
                'phone' => '0998887776',
                'corporate_email' => 'info@transdemo.com',
            ]);

            // Assign activities (A1234B, C5678D)
            foreach (['A1234B', 'C5678D'] as $index => $activityId) {
                $db->table('core.company_activities')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'company_id' => $companyId,
                    'activity_id' => $activityId,
                    'validity' => $db->raw("daterange(CURRENT_DATE, NULL, '[)')"),
                    'is_primary' => $index === 0,
                ]);
            }
        });
    }
}
