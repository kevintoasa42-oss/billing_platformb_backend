<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SriIvaPercentagesSeeder extends Seeder
{
    /**
     * Tipos de IVA vigentes del SRI Ecuador (2026).
     * percentage_code: codigo del SRI (Tabla 4) para el XML.
     */
    public function run(): void
    {
        $now = now();

        $tipos = [
            [
                'code' => 'IVA_15',
                'percentage_code' => '4',
                'name' => 'Tarifa general',
                'percentage' => 15.00,
                'description' => 'Bienes y servicios gravados con tarifa general (vigente desde abril 2024, Ley Organica para Enfrentar el Conflicto Armado Interno).',
            ],
            [
                'code' => 'IVA_8',
                'percentage_code' => '3',
                'name' => 'Tarifa reducida turismo',
                'percentage' => 8.00,
                'description' => 'Servicios turisticos en feriados segun normativa vigente.',
            ],
            [
                'code' => 'IVA_5',
                'percentage_code' => '2',
                'name' => 'Materiales de construccion',
                'percentage' => 5.00,
                'description' => 'Transferencia local de materiales de construccion y servicios de construccion de vivienda de interes social.',
            ],
            [
                'code' => 'IVA_0',
                'percentage_code' => '0',
                'name' => 'Tarifa cero',
                'percentage' => 0.00,
                'description' => 'Bienes y servicios expresamente senalados en los Art. 55 y 56 LRTI (alimentos en estado natural, salud, educacion, exportaciones, etc.).',
            ],
            [
                'code' => 'EXENTO',
                'percentage_code' => '7',
                'name' => 'Exento',
                'percentage' => null,
                'description' => 'Operaciones liberadas del pago del impuesto por norma especifica.',
            ],
            [
                'code' => 'NO_OBJETO',
                'percentage_code' => '6',
                'name' => 'No objeto de IVA',
                'percentage' => null,
                'description' => 'Operaciones fuera del ambito del IVA segun la LRTI.',
            ],
        ];

        foreach ($tipos as $tipo) {
            DB::table('sri_iva_percentages')->updateOrInsert(
                ['code' => $tipo['code']],
                array_merge($tipo, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
