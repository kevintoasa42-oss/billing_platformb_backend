<?php

namespace Database\Seeders\v3;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the initial fiscal sequences for the demo tenant's emission point.
 *
 * Creates rows in fiscal.sequences for each document type supported by the
 * SRI, starting at last_number=0 so the first document issued will be
 * sequential 1 (e.g. 001-001-000000001).
 *
 * Document types (per SRI migration 026):
 *   - invoice            (Factura)
 *   - credit_note        (Nota de Crédito)
 *   - debit_note         (Nota de Débito)
 *   - purchase_settlement (Liquidación de Compra)
 *   - retention          (Comprobante de Retención)
 *   - delivery_note      (Guía de Remisión)
 *
 * Idempotent: skips sequences that already exist (unique constraint on
 * tenant_id, environment, emission_point_id, document_type).
 */
final class V3FiscalSequenceSeeder extends Seeder
{
    private const ENVIRONMENT = 'lab';

    private const DOCUMENT_TYPES = [
        'invoice',
        'credit_note',
        'debit_note',
        'purchase_settlement',
        'retention',
        'delivery_note',
    ];

    public function run(): void
    {
        $db = DB::connection('master_v3');

        $tenant = $db->table('platform.tenants')
            ->where('ruc', V3AuthenticationSeeder::TENANT_RUC)
            ->first(['id']);
        if (! $tenant) {
            $this->command->warn('V3FiscalSequenceSeeder: tenant not found. Run V3AuthenticationSeeder first.');

            return;
        }

        $tenantId = (string) $tenant->id;

        // Get the default emission point for this tenant.
        $emissionPoint = $db->table('core.emission_points')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('is_default', true)
            ->orderBy('sri_code')
            ->first(['id']);

        if (! $emissionPoint) {
            $this->command->warn('V3FiscalSequenceSeeder: default emission point not found. Run V3EstablishmentSeeder first.');

            return;
        }

        $emissionPointId = (string) $emissionPoint->id;

        $db->transaction(function () use ($db, $tenantId, $emissionPointId): void {
            $db->select("SELECT set_config('app.tenant_id', ?, true)", [$tenantId]);

            foreach (self::DOCUMENT_TYPES as $documentType) {
                $exists = $db->table('fiscal.sequences')
                    ->where('tenant_id', $tenantId)
                    ->where('environment', self::ENVIRONMENT)
                    ->where('emission_point_id', $emissionPointId)
                    ->where('document_type', $documentType)
                    ->exists();

                if ($exists) {
                    $this->command->info("V3FiscalSequenceSeeder: sequence {$documentType} already exists, skipping.");

                    continue;
                }

                $db->table('fiscal.sequences')->insert([
                    'tenant_id' => $tenantId,
                    'environment' => self::ENVIRONMENT,
                    'emission_point_id' => $emissionPointId,
                    'document_type' => $documentType,
                    'last_number' => 0,
                ]);
            }
        });
    }
}
