<?php

namespace Database\Seeders\v3;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds products with tax assignments for the demo tenant.
 *
 * Idempotent: skips products that already exist by name.
 */
final class V3ProductSeeder extends Seeder
{
    public function run(): void
    {
        $db = DB::connection('master_v3');

        $tenant = $db->table('platform.tenants')->where('ruc', '1790000000001')->first(['id']);
        if (! $tenant) {
            $this->command->warn('V3ProductSeeder: tenant not found.');

            return;
        }

        $tenantId = (string) $tenant->id;

        $activity = $db->table('core.economic_activities')
            ->where('id', 'A1234B')
            ->first(['id']);

        if (! $activity) {
            $this->command->warn('V3ProductSeeder: economic activity A1234B not found. Run V3EconomicActivitiesSeeder first.');

            return;
        }

        $ivaType = $db->table('core.sri_iva_types')
            ->where('tenant_id', $tenantId)
            ->where('sri_code', 'IVA15')
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

                // Assign IVA tax if type exists
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
                            'sri_code' => 'IVA15',
                            'is_active' => true,
                        ]);
                    }
                }
            }
        });
    }
}
