<?php

namespace Database\Seeders\landlord;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class LandlordDatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SriIvaPercentagesSeeder::class,
            SriPaymentMethodsSeeder::class,
            RolesSeeder::class,
            DatosPruebaSeeder::class,
        ]);
    }
}
