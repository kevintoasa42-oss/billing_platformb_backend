<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SriIvaPercentagesSeeder extends Seeder
{
    /**
     * Tipos de IVA vigentes del SRI Ecuador (2026).
     */
    public function run(): void
    {
        $now = now();

        $tipos = [
            [
                'codigo' => 'IVA_15',
                'nombre' => 'Tarifa general',
                'porcentaje' => 15.00,
                'descripcion' => 'Bienes y servicios gravados con tarifa general (vigente desde abril 2024, Ley Organica para Enfrentar el Conflicto Armado Interno).',
            ],
            [
                'codigo' => 'IVA_8',
                'nombre' => 'Tarifa reducida turismo',
                'porcentaje' => 8.00,
                'descripcion' => 'Servicios turisticos en feriados segun normativa vigente.',
            ],
            [
                'codigo' => 'IVA_5',
                'nombre' => 'Materiales de construccion',
                'porcentaje' => 5.00,
                'descripcion' => 'Transferencia local de materiales de construccion y servicios de construccion de vivienda de interes social.',
            ],
            [
                'codigo' => 'IVA_0',
                'nombre' => 'Tarifa cero',
                'porcentaje' => 0.00,
                'descripcion' => 'Bienes y servicios expresamente senalados en los Art. 55 y 56 LRTI (alimentos en estado natural, salud, educacion, exportaciones, etc.).',
            ],
            [
                'codigo' => 'EXENTO',
                'nombre' => 'Exento',
                'porcentaje' => null,
                'descripcion' => 'Operaciones liberadas del pago del impuesto por norma especifica.',
            ],
            [
                'codigo' => 'NO_OBJETO',
                'nombre' => 'No objeto de IVA',
                'porcentaje' => null,
                'descripcion' => 'Operaciones fuera del ambito del IVA segun la LRTI.',
            ],
        ];

        foreach ($tipos as $tipo) {
            DB::table('sri_iva_percentages')->updateOrInsert(
                ['codigo' => $tipo['codigo']],
                array_merge($tipo, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
