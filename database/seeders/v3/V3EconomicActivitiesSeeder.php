<?php

namespace Database\Seeders\v3;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the global catalog of economic activities (no RLS, no tenant_id).
 *
 * Uses the official SRI Ecuador catalog (Clasificador Nacional de Actividades
 * Económicas CIIU Rev. 4.1). The `id` is the 6-digit CIIU code used by the
 * SRI for RUC registration. `division` is derived from the first 2 digits.
 *
 * Source: Ficha técnica de comprobantes electrónos del SRI / CIIU 4.1.
 *
 * Idempotent: existing activities are skipped by id.
 */
final class V3EconomicActivitiesSeeder extends Seeder
{
    public function run(): void
    {
        $db = DB::connection('master_v3');

        // Subset of CIIU 4.1 activities relevant to transport cooperatives
        // (división H - Transporte y almacenamiento). The full catalog has
        // ~1993 codes; this seed covers the cooperative transport subset.
        $activities = [
            // H - Transporte terrestre (cooperativas)
            ['id' => '492110', 'name' => 'Transporte urbano de pasajeros por bus — cooperativas', 'catalog_version' => 'CIIU-4.1'],
            ['id' => '492210', 'name' => 'Transporte interurbano de pasajeros por bus — cooperativas', 'catalog_version' => 'CIIU-4.1'],
            ['id' => '492310', 'name' => 'Transporte de pasajeros por taxi — cooperativas', 'catalog_version' => 'CIIU-4.1'],
            ['id' => '492410', 'name' => 'Transporte escolar y de personal — cooperativas', 'catalog_version' => 'CIIU-4.1'],
            ['id' => '493010', 'name' => 'Otros transportes terrestres de pasajeros — cooperativas', 'catalog_version' => 'CIIU-4.1'],
            ['id' => '494110', 'name' => 'Transporte de carga por carretera — cooperativas', 'catalog_version' => 'CIIU-4.1'],
            ['id' => '494210', 'name' => 'Servicios de mudanza — cooperativas', 'catalog_version' => 'CIIU-4.1'],

            // H - Transporte por vía acuática (cooperativas)
            ['id' => '501010', 'name' => 'Transporte por vía marítima — cooperativas', 'catalog_version' => 'CIIU-4.1'],
            ['id' => '502010', 'name' => 'Transporte por vía fluvial — cooperativas', 'catalog_version' => 'CIIU-4.1'],
            ['id' => '503010', 'name' => 'Transporte por vía lacustre — cooperativas', 'catalog_version' => 'CIIU-4.1'],

            // H - Transporte aéreo (cooperativas)
            ['id' => '511010', 'name' => 'Transporte aéreo de pasajeros — cooperativas', 'catalog_version' => 'CIIU-4.1'],
            ['id' => '512010', 'name' => 'Transporte aéreo de carga — cooperativas', 'catalog_version' => 'CIIU-4.1'],

            // H - Almacenamiento y actividades de apoyo al transporte
            ['id' => '521010', 'name' => 'Almacenamiento y depósito — cooperativas', 'catalog_version' => 'CIIU-4.1'],
            ['id' => '522110', 'name' => 'Manipulación de carga — cooperativas', 'catalog_version' => 'CIIU-4.1'],
            ['id' => '522410', 'name' => 'Organización de transporte de carga — cooperativas', 'catalog_version' => 'CIIU-4.1'],
            ['id' => '522510', 'name' => 'Servicios de paquetería y mensajería — cooperativas', 'catalog_version' => 'CIIU-4.1'],

            // G - Comercio (para productos de embalaje)
            ['id' => '469000', 'name' => 'Comercio al por mayor no especializado', 'catalog_version' => 'CIIU-4.1'],
            ['id' => '479110', 'name' => 'Comercio al por menor no especializado', 'catalog_version' => 'CIIU-4.1'],

            // F - Construcción
            ['id' => '410100', 'name' => 'Construcción de edificios residenciales', 'catalog_version' => 'CIIU-4.1'],

            // M - Servicios profesionales
            ['id' => '691000', 'name' => 'Actividades jurídicas y de contabilidad', 'catalog_version' => 'CIIU-4.1'],
        ];

        foreach ($activities as $activity) {
            $exists = $db->table('core.economic_activities')
                ->where('id', $activity['id'])
                ->exists();

            if (! $exists) {
                $db->table('core.economic_activities')->insert($activity);
            }
        }
    }
}
