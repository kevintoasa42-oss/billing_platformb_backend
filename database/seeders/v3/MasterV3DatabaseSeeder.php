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
 *   7. Third parties (clientes/proveedores)
 *   8. Carrier companies (needs third parties)
 *   9. Carrier establishments + emission points (needs carrier companies)
 *  10. Carrier affiliations + vehicle assignments (needs third parties + vehicles)
 *  11. Products + tax assignments (needs economic activities + SRI IVA types)
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
            V3ThirdPartySeeder::class,
            V3CarrierCompanySeeder::class,
            V3CarrierEstablishmentSeeder::class,
            V3CarrierAffiliationSeeder::class,
            V3ProductSeeder::class,
        ]);
    }
}
