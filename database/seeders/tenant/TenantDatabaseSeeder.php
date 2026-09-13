<?php

namespace Database\Seeders\tenant;

use Illuminate\Database\Seeder;

final class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            BranchOfficeSeeder::class,
            EmissionPointSeeder::class,
            ClientSeeder::class,
            CarrierSeeder::class,
        ]);
    }
}
