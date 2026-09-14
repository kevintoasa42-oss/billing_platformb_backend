<?php

namespace Database\Seeders\v3;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds the global catalog of economic activities (no RLS, no tenant_id).
 *
 * Idempotent: existing activities are skipped by id.
 */
final class V3EconomicActivitiesSeeder extends Seeder
{
    public function run(): void
    {
        $db = DB::connection('master_v3');

        $activities = [
            ['id' => 'A1234B', 'name' => 'Comercio al por mayor y menor', 'catalog_version' => 'synthetic-lab-v1'],
            ['id' => 'C5678D', 'name' => 'Transporte de mercadería', 'catalog_version' => 'synthetic-lab-v1'],
            ['id' => 'E9012F', 'name' => 'Servicios profesionales', 'catalog_version' => 'synthetic-lab-v1'],
            ['id' => 'G3456H', 'name' => 'Construcción y obras civiles', 'catalog_version' => 'synthetic-lab-v1'],
            ['id' => 'I7890J', 'name' => 'Actividad manufacturera', 'catalog_version' => 'synthetic-lab-v1'],
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
