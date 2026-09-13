<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SriPaymentMethodsSeeder extends Seeder
{
    /**
     * SRI payment methods catalog (Tabla 24).
     * Source: Ficha tecnica de comprobantes electronicos del SRI.
     */
    public function run(): void
    {
        $now = now();

        $methods = [
            [
                'code' => '01',
                'name' => 'Sin utilizacion del sistema financiero',
                'description' => 'Pago en efectivo, sin intervencion de instituciones financieras.',
            ],
            [
                'code' => '15',
                'name' => 'Compensacion de deudas',
                'description' => 'Cancelacion de obligaciones mediante compensacion de deudas.',
            ],
            [
                'code' => '16',
                'name' => 'Tarjeta de debito',
                'description' => 'Pago con tarjeta de debito.',
            ],
            [
                'code' => '17',
                'name' => 'Dinero electronico',
                'description' => 'Pago mediante dinero electronico.',
            ],
            [
                'code' => '18',
                'name' => 'Tarjeta prepago',
                'description' => 'Pago con tarjeta prepago.',
            ],
            [
                'code' => '19',
                'name' => 'Tarjeta de credito',
                'description' => 'Pago con tarjeta de credito.',
            ],
            [
                'code' => '20',
                'name' => 'Otros con utilizacion del sistema financiero',
                'description' => 'Otros medios de pago que utilizan el sistema financiero.',
            ],
            [
                'code' => '21',
                'name' => 'Endoso de titulos',
                'description' => 'Pago mediante endoso de titulos.',
            ],
        ];

        foreach ($methods as $method) {
            DB::table('sri_payment_methods')->updateOrInsert(
                ['code' => $method['code']],
                array_merge($method, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
