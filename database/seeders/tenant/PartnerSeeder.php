<?php

namespace Database\Seeders\tenant;

use App\Context\V1\Partners\Infrastructure\Laravel\Eloquent\Models\PartnerModel;
use Illuminate\Database\Seeder;

final class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        PartnerModel::updateOrCreate(
            ['identification_type' => 'CEDULA', 'identification_number' => '0000000001'],
            [
                'name' => 'Socio',
                'last_name' => 'Principal',
                'email' => 'socio@example.test',
                'phone' => '0990000001',
                'address' => 'Dirección de ejemplo',
                'status' => true,
            ],
        );
    }
}
