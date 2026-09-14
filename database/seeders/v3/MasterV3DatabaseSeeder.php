<?php

namespace Database\Seeders\v3;

use Illuminate\Database\Seeder;

/**
 * Root seeder for the V3 master database.
 *
 * Runs all V3 seeders in dependency order:
 *   1. Authentication (tenant + admin user + membership)
 *   2. Economic activities (global catalog)
 *   3. Company (one per tenant + activities)
 *   4. Establishments + emission points
 *   5. Vehicles
 *   6. SRI IVA types + percentages
 */
final class MasterV3DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            V3AuthenticationSeeder::class,
            V3EconomicActivitiesSeeder::class,
            V3CompanySeeder::class,
            V3EstablishmentSeeder::class,
            V3VehicleSeeder::class,
            V3SriIvaSeeder::class,
        ]);
    }
}
