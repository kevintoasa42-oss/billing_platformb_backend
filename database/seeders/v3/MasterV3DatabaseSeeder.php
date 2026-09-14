<?php

namespace Database\Seeders\v3;

use Illuminate\Database\Seeder;

final class MasterV3DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(V3AuthenticationSeeder::class);
    }
}
