<?php

namespace Database\Seeders\v3;

use Illuminate\Database\Seeder;

/**
 * Root seeder for the V3 master database.
 *
 * Runs all V3 seeders in dependency order:
 *   1. Authentication (tenant + admin user + membership)
 *   2. Economic activities (global catalog, CIIU 4.1)
 *   3. Company (one per tenant + activities)
 *   4. Establishments + emission points
 *   5. SRI IVA types + percentages
 *   6. Third parties (clientes/proveedores)
 *   7. Products + tax assignments (needs economic activities + SRI IVA types)
 *
 * Note: Carrier seeders are intentionally omitted. The default demo
 * company starts without carriers/socios. Carrier data should be
 * created through the Carrier onboarding API when needed.
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
            V3SriIvaSeeder::class,
            V3ThirdPartySeeder::class,
            V3ProductSeeder::class,
        ]);
    }
}
