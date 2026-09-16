<?php

namespace Database\Seeders\v3;

use Database\Seeders\v3\V3AuthenticationSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds products with tax assignments for the demo tenant.
 *
 * Uses the official SRI IVA code '4' (IVA 15%, tarifa general vigente desde
 * 2024-01-01) and real CIIU activity codes from V3EconomicActivitiesSeeder.
 *
 * Idempotent: skips products that already exist by name.
 */
final class V3ProductSeeder extends Seeder
{
    public function run(): void
    {
        $db = DB::connection('master_v3');

        $tenant = $db->table('platform.tenants')->where("ruc", V3AuthenticationSeeder::TENANT_RUC)->first(['id']);
        if (! $tenant) {
            $this->command->warn('V3ProductSeeder: tenant not found.');

            return;
        }

        $tenantId = (string) $tenant->id;

        // Use a real CIIU activity code seeded by V3EconomicActivitiesSeeder.
        // 494110 = Transporte de carga por carretera — cooperativas.
        $activity = $db->table('core.economic_activities')
            ->where('id', '494110')
            ->first(['id']);

        if (! $activity) {
            $this->command->warn('V3ProductSeeder: economic activity 494110 not found. Run V3EconomicActivitiesSeeder first.');

            return;
        }

        // SRI IVA type code '4' = IVA 15% (tarifa general vigente desde 2024).
        $ivaType = $db->table('core.sri_iva_types')
            ->where('tenant_id', $tenantId)
            ->where('sri_code', '4')
            ->first(['id']);

        $products = [
            ['name' => 'Servicio de Transporte Nacional', 'unit_price' => 150.00, 'type' => 'service', 'barcode' => 'SVC001', 'description' => 'Servicio de transporte de carga nacional'],
            ['name' => 'Flete Internacional', 'unit_price' => 2500.00, 'type' => 'service', 'barcode' => 'SVC002', 'description' => 'Servicio de flete internacional'],
            ['name' => 'Caja de Cartón 50x50', 'unit_price' => 2.50, 'type' => 'product', 'barcode' => 'PRD001', 'description' => 'Caja de cartón para embalaje'],
            ['name' => 'Pallet de Madera', 'unit_price' => 15.00, 'type' => 'product', 'barcode' => 'PRD002', 'description' => 'Pallet estándar de madera'],
        ];

        $db->transaction(function () use ($db, $tenantId, $activity, $ivaType, $products): void {
            $db->select("SELECT set_config('app.tenant_id', ?, true)", [$tenantId]);

            foreach ($products as $prod) {
                $exists = $db->table('core.products')
                    ->where('tenant_id', $tenantId)
                    ->where('name', $prod['name'])
                    ->exists();

                if ($exists) {
                    $this->command->info("V3ProductSeeder: product name={$prod['name']} already exists, skipping.");

                    continue;
                }

                $productId = (string) Str::uuid();
                $db->table('core.products')->insert([
                    'id' => $productId,
                    'tenant_id' => $tenantId,
                    'name' => $prod['name'],
                    'activity_id' => $activity->id,
                    'unit_price' => $prod['unit_price'],
                    'type' => $prod['type'],
                    'barcode' => $prod['barcode'],
                    'description' => $prod['description'],
                    'is_active' => true,
                ]);

                // Assign IVA 15% (SRI code '4') if the type exists.
                if ($ivaType) {
                    $taxExists = $db->table('core.product_tax_assignments')
                        ->where('tenant_id', $tenantId)
                        ->where('product_id', $productId)
                        ->where('sri_iva_type_id', $ivaType->id)
                        ->exists();

                    if (! $taxExists) {
                        $db->table('core.product_tax_assignments')->insert([
                            'id' => (string) Str::uuid(),
                            'tenant_id' => $tenantId,
                            'product_id' => $productId,
                            'sri_iva_type_id' => $ivaType->id,
                            'tax_name' => 'IVA 15%',
                            'percentage' => 15,
                            'sri_code' => '4',
                            'is_active' => true,
                        ]);
                    }
                }
            }
        });
    }
}
