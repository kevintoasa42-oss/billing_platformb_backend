<?php

namespace Database\Seeders\tenant;

use App\Context\V1\Modules\Clients\Infrastructure\Laravel\Eloquent\Models\ClientModel;
use Illuminate\Database\Seeder;

final class ClientSeeder extends Seeder
{
    public function run(): void
    {
        ClientModel::updateOrCreate(
            [
                'identification_type' => 'CONSUMIDOR_FINAL',
                'identification_number' => '9999999999999',
            ],
            [
                'name' => 'Consumidor',
                'last_name' => 'Final',
                'status' => 'ACTIVE',
                'address' => 'No especificada',
                'phone' => '0000000000',
                'email' => 'consumidor.final@example.com',
                'type' => 'NATURAL',
                'plates' => null,
            ],
        );
    }
}
